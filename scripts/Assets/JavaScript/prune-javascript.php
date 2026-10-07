<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/BuildLock.php';
require_once __DIR__ . '/Support/JavaScriptRetention.php';
$root = dirname(__DIR__, 3);
$arguments = array_slice($argv, 1);
if (array_diff($arguments, ['--force']) !== []) throw new InvalidArgumentException('Usage: composer js:prune [-- --force]');
$force = in_array('--force', $arguments, true);
BuildLock::acquire($root);
$manifest = require $root . '/Config/assets/javascript-manifest.php';
if (!isset($manifest['files']) || !is_array($manifest['files']) || $manifest['files'] === [])
    throw new RuntimeException('Missing deployed JavaScript manifest.');
foreach ($manifest['files'] as $file)
{
    if (!is_string($file) || !is_file($root . '/public/' . $file))
        throw new RuntimeException('Incomplete deployment: active bundle missing. Cleanup cancelled.');
}
$removed = JavaScriptRetention::prune($root, $manifest['files'], force: $force);
$assets = require $root . '/Config/assets/versions.php';
foreach (array_keys($assets) as $path)
{
    if (str_starts_with($path, 'js/dist/') && !is_file($root . '/public/' . $path)) unset($assets[$path]);
}
AtomicFile::writeIfChanged($root . '/Config/assets/versions.php', "<?php\n\ndeclare(strict_types=1);\n\n// Generated asset versions.\nreturn " . var_export($assets, true) . ";\n");
echo 'Retired JavaScript files removed: ' . $removed . ($force ? ' (force ; active files preserved).' : ' (7 days since observed on this server).') . PHP_EOL;
if ($force) echo 'Recharger les pages deja ouvertes pour utiliser les bundles actifs.' . PHP_EOL;
$retired = json_decode((string) file_get_contents($root . '/storage/javascript-retention.json'), true, 512, JSON_THROW_ON_ERROR);
if ($retired === [])
{
    echo 'Aucun ancien fichier en attente de suppression.' . PHP_EOL;
}
else
{
    $nextRemoval = min(array_values($retired)) + JavaScriptRetention::DAYS * 86400;
    $minutes = (int) ceil(max(0, $nextRemoval - time()) / 60);
    $days = intdiv($minutes, 1440);
    $hours = intdiv($minutes % 1440, 60);
    $remainingMinutes = $minutes % 60;
    echo 'Anciens fichiers conserves : ' . count($retired) . PHP_EOL;
    echo "Prochaine suppression possible dans : $days j $hours h $remainingMinutes min." . PHP_EOL;
    echo 'Relancer composer js:prune apres ce delai ; la suppression reste manuelle.' . PHP_EOL;
}
