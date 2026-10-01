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
foreach (['Figurine', 'Nendoroid', 'Peluche', 'Artbook'] as $kind)
{
    $table = strtolower($kind);
    $figurineFields = $kind === 'Figurine' ? 'scale VARCHAR(30), height_cm DOUBLE,' : '';
    $domain = $kind === 'Artbook' ? 'Manga' : $kind;
    $nameField = $kind === 'Artbook' ? 'artbook' : 'waifu';
    $detailFields = $kind === 'Artbook'
        ? 'auteur VARCHAR(150), serie VARCHAR(150), lu TINYINT NOT NULL DEFAULT 0,'
        : "origin VARCHAR(150), $figurineFields";
    $db->exec("CREATE TEMPORARY TABLE $table (
        id INT PRIMARY KEY, slug VARCHAR(150), numero INT,
        $nameField VARCHAR(150), $detailFields
        company VARCHAR(150) NOT NULL DEFAULT '', release_date DATE NULL, commentaire TEXT NULL,
        UNIQUE KEY (slug, numero)
    ) ENGINE=InnoDB");
    try
    {
        $repository = $container->get("App\\Repositories\\$domain\\{$kind}Repository");
        $service = $container->get("App\\Services\\$domain\\{$kind}WriteService");
        $dtoClass = "App\\DTO\\$domain\\Inputs\\{$kind}UpdateDTO";
        $dto = $dtoClass::fromArray([$nameField => 'Updated', 'source' => 'Updated source', 'origin' => 'Fixture', 'scale' => '1/7', 'company' => 'Fixture']);
        $method = 'update' . $kind;
        try
        {
            $repository->$method('fixture', 1, $dto);
            throw new RuntimeException('Update accepted outside a transaction.');
        }
        catch (LogicException) {}

        $db->exec("INSERT INTO $table (id, slug, numero, $nameField) VALUES (1, 'fixture', 1, 'Original')");
        $check($service->update('fixture', 1, $dto)->success, "$kind: valid update failed");
        $check($db->query("SELECT $nameField FROM $table WHERE id = 1")->fetchColumn() === 'Updated', "$kind: update not saved");
        $check($service->update('fixture', 1, $dto)->success, "$kind: unchanged update failed");

        if ($kind === 'Artbook')
        {
            $book = $repository->findOneBySlugAndNumero('fixture', 1);
            $check($book->auteur === 'Updated source' && $book->serie === null, 'Author source not preserved');
            $db->exec("UPDATE artbook SET auteur = NULL, serie = 'Original series' WHERE id = 1");
            $check($service->update('fixture', 1, $dto)->success, 'Series source update failed');
            $book = $repository->findOneBySlugAndNumero('fixture', 1);
            $check($book->auteur === null && $book->serie === 'Updated source', 'Series source not preserved');
            try
            {
                $repository->updateReadStatus('fixture', 1, true);
                throw new RuntimeException('Read status accepted outside a transaction');
            }
            catch (LogicException) {}
            $before = $db->transaction(fn () => $repository->updateReadStatus('fixture', 1, true));
            $check($before instanceof \App\Models\Artbook && !$before->lu, 'Original unread state lost');
            $before = $db->transaction(fn () => $repository->updateReadStatus('fixture', 1, true));
            $check($before instanceof \App\Models\Artbook && $before->lu, 'Unchanged read state lost');
            $check($repository->findOneBySlugAndNumero('fixture', 1)->lu, 'Read status not saved');
        }

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
        if ($kind === 'Artbook')
        {
            try
            {
                $service->updateReadStatus('fixture', 1, 1);
                throw new RuntimeException('Missing read target must return 404');
            }
            catch (NotFoundException $error)
            {
                $check($error->getStatusCode() === 404, 'Wrong missing read target status');
            }
            try
            {
                $db->transaction(fn () => $repository->updateReadStatus('fixture', 1, true));
                throw new RuntimeException('Missing read target reported as updated');
            }
            catch (NotFoundException) {}
        }
    }
    finally
    {
        if ($db->inTransaction()) $db->rollBack();
        $db->exec("DROP TEMPORARY TABLE $table");
    }
}
echo "PASS: four collection updates require transactions, preserve no-op success and return 404 after deletion; artbook sources and read status preserved.\n";
