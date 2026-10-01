<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Http\Exceptions\NotFoundException;

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};

// MySQL temporary tables shadow application tables on this connection only.
foreach (['Figurine', 'Nendoroid', 'Peluche'] as $kind)
{
    $table = strtolower($kind);
    $figurineFields = $kind === 'Figurine' ? 'scale VARCHAR(30), height_cm DOUBLE,' : '';
    $db->exec("CREATE TEMPORARY TABLE $table (
        id INT PRIMARY KEY, slug VARCHAR(150), numero INT,
        waifu VARCHAR(150), origin VARCHAR(150), $figurineFields
        company VARCHAR(150), release_date DATE NULL, commentaire TEXT NULL,
        UNIQUE KEY (slug, numero)
    ) ENGINE=InnoDB");
    try
    {
        $repository = $container->get("App\\Repositories\\$kind\\{$kind}Repository");
        $service = $container->get("App\\Services\\$kind\\{$kind}WriteService");
        $dtoClass = "App\\DTO\\$kind\\Inputs\\{$kind}UpdateDTO";
        $dto = $dtoClass::fromArray(['waifu' => 'Updated', 'origin' => 'Fixture', 'scale' => '1/7', 'company' => 'Fixture']);
        $method = 'update' . $kind;
        try
        {
            $repository->$method('fixture', 1, $dto);
            throw new RuntimeException('Update accepted outside a transaction.');
        }
        catch (LogicException) {}

        $db->exec("INSERT INTO $table (id, slug, numero, waifu) VALUES (1, 'fixture', 1, 'Original')");
        $check($service->update('fixture', 1, $dto)->success, "$kind: valid update failed");
        $check($db->query("SELECT waifu FROM $table WHERE id = 1")->fetchColumn() === 'Updated', "$kind: update not saved");
        $check($service->update('fixture', 1, $dto)->success, "$kind: unchanged update failed");

        // The controller can have observed an object before a competing deletion.
        $check($repository->findOneBySlugAndNumero('fixture', 1) !== null, 'Initial read failed');
        $db->exec("DELETE FROM $table WHERE id = 1");
        try
        {
            $service->update('fixture', 1, $dto);
            throw new RuntimeException("$kind: disappeared row reported as successful");
        }
        catch (NotFoundException $error)
        {
            $check($error->getStatusCode() === 404, 'Wrong missing-row status');
        }
        $check(!$db->inTransaction(), 'Failed update left a transaction open');
        $check((int)$db->query("SELECT COUNT(*) FROM $table")->fetchColumn() === 0, 'Missing row recreated');
    }
    finally
    {
        if ($db->inTransaction()) $db->rollBack();
        $db->exec("DROP TEMPORARY TABLE $table");
    }
}
echo "PASS: three collection updates require transactions, preserve no-op success and return 404 after deletion.\n";
