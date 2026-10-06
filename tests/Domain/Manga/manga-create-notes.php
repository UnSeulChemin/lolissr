<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
require ROOT . '/App/Support/Helpers.php';

use App\DTO\Manga\Inputs\MangaCreateData;
use App\DTO\Media\UploadThumbnailData;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Services\Manga\MangaWriteService;

use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;
use Framework\Http\Requests\Request;

Bootstrap::loadEnvOnly();
$check = static function (bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
};
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$db->exec('CREATE TEMPORARY TABLE create_notes_fixture LIKE manga');
$db->exec('ALTER TABLE create_notes_fixture RENAME TO manga');
try
{
    $service = $container->get(MangaWriteService::class);
    $persist = new ReflectionMethod($service, 'createManga');
    foreach ([[4, 5, 9], [null, null, null], [3, null, null], [null, 2, null]] as $index => [$cover, $book, $total])
    {
        $input = ['livre' => 'Fixture', 'slug' => 'notes-fixture', 'statut' => 'en_cours',
            'numero' => $index + 1, 'jacquette' => $cover ?? '', 'livre_note' => $book ?? ''];
        $dto = MangaCreateData::fromArray($input);
        $check($dto->jacquette === $cover && $dto->livreNote === $book, 'DTO lost optional notes');
        $result = $db->transaction(fn () => $persist->invoke($service, $dto, new UploadThumbnailData('fixture', 'webp', 'unused')));
        $check($result === null, 'Creation failed');
        $row = $db->query('SELECT jacquette, livre_note, note FROM manga WHERE numero = ' . ($index + 1))->fetch(PDO::FETCH_ASSOC);
        $check($row === ['jacquette' => $cover, 'livre_note' => $book, 'note' => $total], 'Persisted notes or total differ');
    }
    foreach (['jacquette', 'livre_note'] as $field)
    {
        foreach ([0, 6, 'abc', ['invalid']] as $invalid)
        {
            $request = new MangaCreateRequest(new Request(post: [$field => $invalid]));
            $check(isset($request->errors()[$field]), 'Invalid note accepted');
        }
        $request = new MangaCreateRequest(new Request(post: [$field => '']));
        $check(!isset($request->errors()[$field]), 'Empty optional note rejected');
    }
}
finally
{
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('DROP TEMPORARY TABLE manga');
}
echo "PASS: manga creation persists selected/optional notes and coherent totals; invalid ratings rejected. Temporary table only.\n";
