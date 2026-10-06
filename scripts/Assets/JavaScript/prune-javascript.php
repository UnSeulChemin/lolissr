<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Support/BuildLock.php';
require_once __DIR__ . '/Support/JavaScriptRetention.php';
$root = dirname(__DIR__, 3);
BuildLock::acquire($root);
$manifest = require $root . '/Config/javascript.php';
if (!isset($manifest['files']) || !is_array($manifest['files']) || $manifest['files'] === [])
    throw new RuntimeException('Missing deployed JavaScript manifest.');
foreach ($manifest['files'] as $file)
{
    if (!is_string($file) || !is_file($root . '/public/' . $file))
        throw new RuntimeException('Incomplete deployment: active bundle missing. Cleanup cancelled.');
}
$removed = JavaScriptRetention::prune($root, $manifest['files']);
$assets = require $root . '/Config/assets.php';
foreach (array_keys($assets) as $path)
{
    if (str_starts_with($path, 'js/dist/') && !is_file($root . '/public/' . $path)) unset($assets[$path]);
}
AtomicFile::writeIfChanged($root . '/Config/assets.php', "<?php\n\ndeclare(strict_types=1);\n\n// Generated asset versions.\nreturn " . var_export($assets, true) . ";\n");
echo 'Retired JavaScript files removed: ' . $removed . ' (7 days since observed on this server).' . PHP_EOL;
