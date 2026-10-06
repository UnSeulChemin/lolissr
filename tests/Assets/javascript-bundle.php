<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';
// Render the anonymous layout without accessing authentication or the database.
function user(): ?\App\Models\User\User
{ return null; }
function csrf_token(): string
{ return 'bundle-layout-test'; }
require ROOT . '/App/Support/Helpers.php';

$root = dirname(__DIR__, 2);
$manifest = require $root . '/Config/assets/javascript-manifest.php';
$metadata = require $root . '/scripts/Assets/JavaScript/source-manifest.php';
if (isset($manifest['sources']) || $metadata['sources'] === [])
    throw new RuntimeException('JavaScript source metadata must be separate and nonempty.');
if (hash('sha256', serialize($manifest)) !== $metadata['manifest_hash'])
    throw new RuntimeException('JavaScript manifest and source metadata differ: run composer assets:build');
foreach ($metadata['sources'] as $path => $hash)
{
    if (!is_file($root . '/' . $path) || hash_file('sha256', $root . '/' . $path) !== $hash)
        throw new RuntimeException('Stale JavaScript bundle: run composer assets:build');
}
foreach ($manifest['files'] as $path)
{
    if (!is_file($root . '/public/' . $path)) throw new RuntimeException('Missing bundle chunk: ' . $path);
}
foreach ([$manifest['entry'], ...$manifest['preloads']] as $path)
{
    if (!in_array($path, $manifest['files'], true)) throw new RuntimeException('Unknown startup module: ' . $path);
}
foreach (['local', 'production'] as $environment)
{
    \Framework\Config\Environment::set('APP_ENV', $environment);
    \Framework\Config\Config::clear();
    $view = new \App\DTO\Common\Responses\ViewData('/lolissr/', new \App\DTO\Common\Responses\FlashToastData(null, null));
    $pageStylesheets = [];
    $content = '<p>Fixture</p>';
    ob_start();
    require ROOT . '/App/Views/layouts/base.php';
    $html = (string)ob_get_clean();
    $expected = $environment === 'production' ? $manifest['entry'] : 'js/app.js';
    if (!str_contains($html, 'src="/lolissr/' . $expected . '?v=')) throw new RuntimeException('Wrong script for ' . $environment);
    if ($environment === 'production' && (str_contains($html, 'type="importmap"') || substr_count($html, 'rel="modulepreload"') !== count($manifest['preloads'])))
        throw new RuntimeException('Production imports/preloads are inconsistent');
}
echo 'PASS: current JavaScript bundle, ' . count($manifest['files']) . " output files, development/production layouts and startup manifest.\n";
