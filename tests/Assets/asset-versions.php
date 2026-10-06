<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';
\Framework\Config\Env::set('APP_ENV', 'production');
\Framework\Config\Config::clear();

$manifest = require ROOT . '/Config/assets/versions.php';
foreach ($manifest as $path => $version)
{
    if (! is_file(ROOT . '/public/' . $path) || hash_file('sha256', ROOT . '/public/' . $path) !== $version)
    {
        throw new RuntimeException('Stale asset manifest; run php scripts/Assets/build-assets.php: ' . $path);
    }
    if (\App\Support\Assets\AssetVersions::version($path) !== $version)
    {
        throw new RuntimeException('Wrong asset version: ' . $path);
    }
}
echo 'PASS: ' . count($manifest) . " asset versions match their files.\n";
