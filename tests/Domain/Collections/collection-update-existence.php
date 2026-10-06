<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';

use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Http\Exceptions\NotFoundException;

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$check = static function (bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
};

// MySQL temporary tables shadow application tables on this connection only.
foreach (['Figurine', 'Nendoroid', 'Peluche', 'Artbook'] as $kind)
{
    $table = strtolower($kind);
    $figurineFields = $kind === 'Figurine' ? 'scale VARCHAR(30), height_cm DOUBLE,' : '';
    $domain = $kind;
    $nameField = $kind === 'Artbook' ? 'artbook' : 'waifu';
    $detailFields = $kind === 'Artbook'
        ? 'auteur VARCHAR(150), serie VARCHAR(150), lu TINYINT NOT NULL DEFAULT 0,'
        : "origin VARCHAR(150), $figurineFields";
    $db->exec(owned_fixture_sql($db, "CREATE TEMPORARY TABLE $table (
        id INT PRIMARY KEY, slug VARCHAR(150), numero INT,
        $nameField VARCHAR(150), $detailFields
        company VARCHAR(150) NOT NULL DEFAULT '', release_date DATE NULL, commentaire TEXT NULL,
        UNIQUE KEY (slug, numero)
    ) ENGINE=InnoDB"));
    try
    {
        $repository = $container->get("App\\Repositories\\$domain\\{$kind}Repository");
        $service = $container->get("App\\Services\\$domain\\{$kind}WriteService");
        $dtoClass = "App\\DTO\\$domain\\Inputs\\{$kind}UpdateData";
        $dto = $dtoClass::fromArray([$nameField => 'Updated', 'source' => 'Updated source', 'origin' => 'Fixture', 'scale' => '1/7', 'company' => 'Fixture']);
        $method = 'update' . $kind;
        try
        {
            $repository->$method('fixture', 1, $dto);
            throw new RuntimeException('Update accepted outside a transaction.');
        }
        catch (LogicException)
        {}

        $db->exec(owned_fixture_sql($db, "INSERT INTO $table (id, slug, numero, $nameField) VALUES (1, 'fixture', 1, 'Original')"));
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
            catch (LogicException)
            {}
            $before = $db->transaction(fn () => $repository->updateReadStatus('fixture', 1, true));
            $check($before instanceof \App\Models\Artbook\Artbook && !$before->lu, 'Original unread state lost');
            $before = $db->transaction(fn () => $repository->updateReadStatus('fixture', 1, true));
            $check($before instanceof \App\Models\Artbook\Artbook && $before->lu, 'Unchanged read state lost');
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
            catch (NotFoundException)
            {}
        }
    }
    finally
    {
        if ($db->inTransaction()) $db->rollBack();
        $db->exec("DROP TEMPORARY TABLE $table");
    }
}
final class NoteQueryCounter extends PDOStatement
{
    public static int $executions = 0;
    public function execute(?array $params = null): bool
    {
        self::$executions++;
        return parent::execute($params);
    }
}
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [NoteQueryCounter::class]);
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE manga (id INT PRIMARY KEY, slug VARCHAR(150), numero INT,
    jacquette INT NULL, livre_note INT NULL, note INT NULL, UNIQUE KEY (slug, numero)) ENGINE=InnoDB'));
try
{
    $db->exec(owned_fixture_sql($db, "INSERT INTO manga VALUES (1, 'fixture', 1, 1, 1, 2), (2, 'fixture', 2, 1, 1, 2)"));
    $repository = $container->get(\App\Repositories\Manga\MangaRepository::class);
    $service = $container->get(\App\Services\Manga\MangaWriteService::class);
    try
    {
        $repository->updateNote('fixture', 1, 4, 5);
        throw new RuntimeException('Notes accepted outside a transaction');
    }
    catch (LogicException)
    {}
    foreach ([[4, 5, 9], [4, 5, 9], [null, 3, null], [null, null, null]] as [$cover, $book, $stored])
    {
        NoteQueryCounter::$executions = 0;
        $result = $service->updateNote('fixture', 1, new \App\DTO\Manga\Inputs\MangaUpdateNoteData($cover, $book));
        $check($result->success && NoteQueryCounter::$executions === 2, 'Note update must use two queries');
        $notes = $result->data['notes'];
        $check($notes->jacquette === ($cover ?? 0) && $notes->livreNote === ($book ?? 0)
            && $notes->note === ($cover ?? 0) + ($book ?? 0), 'Note response changed');
        $row = $db->query('SELECT jacquette, livre_note, note FROM manga WHERE id = 1')->fetch();
        $check($row->jacquette === $cover && $row->livre_note === $book && $row->note === $stored, 'Stored notes changed');
        $check((int) $db->query('SELECT note FROM manga WHERE id = 2')->fetchColumn() === 2, 'Other volume changed');
    }
    $db->exec('DELETE FROM manga WHERE id = 1');
    try
    {
        $service->update('fixture', 1, \App\DTO\Manga\Inputs\MangaUpdateData::fromArray(['statut' => 'en_cours']));
        throw new RuntimeException('Missing full-update target accepted');
    }
    catch (NotFoundException $error)
    {
        $check($error->getStatusCode() === 404 && !$db->inTransaction(), 'Missing full-update target must roll back with 404');
        $check((int) $db->query('SELECT COUNT(*) FROM manga WHERE id = 2')->fetchColumn() === 1, 'Other tome was modified');
    }
    try
    {
        $service->updateNote('fixture', 1, new \App\DTO\Manga\Inputs\MangaUpdateNoteData(4, 5));
        throw new RuntimeException('Missing note target accepted');
    }
    catch (NotFoundException $error)
    {
        $check($error->getStatusCode() === 404 && !$db->inTransaction(), 'Missing note target must roll back with 404');
    }
}
finally
{
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('DROP TEMPORARY TABLE manga');
}
echo "PASS: collection updates, artbook sources/read status and two-query manga notes (nulls, no-op, missing target).\n";

// A deletion after the controller's lookup must still produce a 404 in the service.
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE chinois_grammaire (
    id INT PRIMARY KEY, niveau TEXT, section TEXT, categorie TEXT, titre TEXT,
    structure TEXT, abreviation TEXT, phrase TEXT, pinyin TEXT, traduction TEXT,
    explication TEXT, position INT, maitrise INT, xp_rewarded INT
) ENGINE=InnoDB'));
try
{
    $grammarService = $container->get(\App\Services\Chinois\ChinoisWriteService::class);
    $dto = \App\DTO\Chinois\Inputs\ChinoisGrammaireCreateData::fromArray(['niveau' => 'HSK1']);
    try
    {
        $grammarService->updateGrammaire(1, $dto);
        throw new RuntimeException('Missing grammar did not throw 404');
    }
    catch (NotFoundException $error)
    {
        $check($error->getStatusCode() === 404 && !$db->inTransaction(), 'Missing grammar must roll back with 404');
    }
    $lockName = 'grammar-order:' . substr(hash('sha256', \Framework\Config\DatabaseConfig::name()), 0, 40);
    $statement = $db->prepare('SELECT IS_FREE_LOCK(?)');
    $statement->execute([$lockName]);
    $check((int) $statement->fetchColumn() === 1, 'Missing grammar leaked the ordering lock');
}
finally
{
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('DROP TEMPORARY TABLE chinois_grammaire');
}
echo "PASS: missing grammar returns 404, rolls back and releases the ordering lock.\n";
