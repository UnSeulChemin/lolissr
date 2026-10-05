<?php

declare(strict_types=1);

require dirname(__DIR__) . '/Support/bootstrap.php';
require ROOT . '/App/Support/Helpers.php';
\Framework\Application\Bootstrap::loadEnvOnly();
$container = new \Framework\Container\Container();
$container->singleton(\Framework\Database\Database::class);
$db = $container->get(\Framework\Database\Database::class);
$db->exec('CREATE TEMPORARY TABLE acquire_fixture LIKE manga');
$db->exec('ALTER TABLE acquire_fixture RENAME TO manga');
$id = substr(bin2hex(random_bytes(8)), 0, 8) . '-1234-1234-1234-' . bin2hex(random_bytes(6));
$source = ROOT . '/public/images/manga/upcoming/' . $id . '.jpg';
$path = tempnam(sys_get_temp_dir(), 'acquire-catalog-');
$created = [];
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
try
{
    if (!is_dir(dirname($source))) mkdir(dirname($source), 0755, true);
    $image = imagecreatetruecolor(32, 48);
    imagejpeg($image, $source);
    imagedestroy($image);
    $originalHash = hash_file('sha256', $source);
    $originalBytes = file_get_contents($source);
    $entry = static fn (int $n): array => ['number' => $n, 'release_date' => date('Y-m-d'), 'id' => $id];
    file_put_contents($path, json_encode(['users' => ['1' => ['fixture' => ['upcoming' => [$entry(2), $entry(3)]]]]], JSON_THROW_ON_ERROR));
    $db->exec("INSERT INTO manga (user_id, thumbnail, extension, slug, livre, numero, editeur, statut) VALUES
        (1, 'fixture', 'jpg', 'fixture', 'Mon titre', 1, 'Mon editeur', 'en_cours'),
        (2, 'fixture', 'jpg', 'fixture', 'Titre etranger', 1, 'Autre editeur', 'termine')");
    $service = $container->get(\App\Services\Manga\MangaWriteService::class);
    $releases = new \App\Services\Manga\UpcomingMangaService(new \App\Repositories\Manga\MangaRepository($db), $path);
    $result = $service->acquireRelease('fixture', 2, $releases);
    $row = $db->query('SELECT * FROM manga WHERE user_id = 1 AND numero = 2')->fetch(PDO::FETCH_ASSOC);
    if (is_array($row)) $created[] = ROOT . '/public/images/manga/thumbnail/' . $row['thumbnail'] . '.' . $row['extension'];
    $check($result->success && is_array($row), 'Acquisition failed');
    $check(str_starts_with($row['thumbnail'], 'mon-titre-02'), 'Cover name lost series/volume convention');
    $check($row['livre'] === 'Mon titre' && $row['editeur'] === 'Mon editeur' && $row['statut'] === 'en_cours', 'Series metadata or owner changed');
    $check((int) $row['lu'] === 0 && (int) $row['xp_read_rewarded'] === 0 && $row['note'] === null, 'Acquisition granted reading/ratings');
    $converted = getimagesize($created[0]);
    $check($row['extension'] === 'webp' && $converted !== false && $converted[2] === IMAGETYPE_WEBP && $converted[0] === 32 && $converted[1] === 48, 'Cover not converted to WebP with original dimensions');
    $check(hash_file('sha256', $source) === $originalHash, 'Shared source damaged');
    $check(array_column($releases->forSeries('fixture'), 'number') === [3], 'Purchased release still appears');
    $check($service->acquireRelease('fixture', 2, $releases)->status === 409, 'Duplicate acquisition accepted');
    $owner = new \App\Models\User(); $owner->id = 2;
    $GLOBALS['testCurrentUser'] = $owner;
    $check($service->acquireRelease('fixture', 3, $releases)->status === 404, 'Foreign catalog accessible');
    $check((int) $db->query('SELECT COUNT(*) FROM manga WHERE user_id = 2')->fetchColumn() === 1, 'Foreign collection changed');
    $GLOBALS['testCurrentUser'] = null;
    $check($service->acquireRelease('fixture', 3, $releases)->status === 401, 'Anonymous acquisition accepted');
    unset($GLOBALS['testCurrentUser']);
    $check($service->acquireRelease('../fixture', 3, $releases)->status === 404, 'Unknown/path input accepted');
    $check($service->acquireRelease('fixture', 1000, $releases)->status === 422, 'Invalid number accepted');
    unlink($source);
    $check($service->acquireRelease('fixture', 3, $releases)->status === 422, 'Missing cover accepted');
    file_put_contents($source, $originalBytes);
    $db->exec('ALTER TABLE manga ADD CONSTRAINT chk_acquire_fixture CHECK (numero <> 3)');
    $before = glob(ROOT . '/public/images/manga/thumbnail/*.webp') ?: [];
    try
    {
        $service->acquireRelease('fixture', 3, $releases);
        throw new RuntimeException('Expected insertion failure');
    }
    catch (PDOException $error) { $check(($error->errorInfo[1] ?? null) === 3819, 'Unexpected insertion error'); }
    $check((glob(ROOT . '/public/images/manga/thumbnail/*.webp') ?: []) === $before, 'Failed insertion leaked a copied cover');
    $check(hash_file('sha256', $source) === $originalHash, 'Rollback removed shared cover');
    $check($service->delete('fixture', 2)->success && !is_file($created[0]) && is_file($source), 'Deleting owned volume damaged shared cover');
    echo "PASS: local cover acquisition, series metadata, unread/no-XP state, owner isolation, duplicates, missing covers, rollback cleanup and independent image deletion. Temporary table only.\n";
}
finally
{
    unset($GLOBALS['testCurrentUser']);
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('DROP TEMPORARY TABLE manga');
    foreach ($created as $file) if (is_file($file)) unlink($file);
    if (is_file($source)) unlink($source);
    if (is_string($path) && is_file($path)) unlink($path);
}
