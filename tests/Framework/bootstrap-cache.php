<?php

declare(strict_types=1);
require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';
require ROOT . '/scripts/Support/AtomicFile.php';

use Framework\Application\BootstrapCache;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Config\EnvironmentValidator;
use Framework\Container\Container;
use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

$check = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
$directory = sys_get_temp_dir() . '/bootstrap-cache-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$path = $directory . '/compiled.php';
Env::load(ROOT . '/.env.example');
Env::set('DB_NAME', 'fixture');
Env::set('DB_USER', 'fixture');
Env::set('APP_ENV', 'production');
Env::set('APP_DEBUG', false);
Env::set('PROFILER_ENABLED', false);
EnvironmentValidator::validate();
try
{
    $check(BootstrapCache::load($path) === null, 'Missing cache did not fall back.');
    AtomicFile::writeIfChanged($path, BootstrapCache::compile(), 0600);
    $cached = BootstrapCache::load($path);
    $check($cached !== null, 'Compiled cache did not load.');
    Env::set('UPLOAD_MAX_PIXELS', 0);
    $check(BootstrapCache::load($path) === null, 'Validation-only input did not invalidate cache.');
    try
    {
        BootstrapCache::compile();
        throw new LogicException('Invalid environment compiled.');
    }
    catch (RuntimeException) {}
    Env::load(ROOT . '/.env.example');
    Env::set('DB_NAME', 'fixture');
    Env::set('DB_USER', 'fixture');
    Env::set('APP_ENV', 'production');
    Env::set('APP_DEBUG', false);
    Env::set('PROFILER_ENABLED', false);
    $check(BootstrapCache::load($path) !== null, 'Unchanged environment did not restore cache.');
    $expected = new RouteCollection();
    $register = require ROOT . '/Config/routes.php';
    $register(new Router($expected, new Container()));
    $check(serialize($expected) === serialize($cached['routes']), 'Compiled routes changed order, patterns or middleware.');
    $check($cached['routes']->allowedMethodsFor('/sql') === [], 'SQL tool exposed in production.');
    $check($cached['routes']->allowedMethodsFor('/inscription') === [], 'Registration exposed in production.');
    Config::prime($cached['config']);
    $check(Config::get('database.name') === 'fixture', 'Configuration not primed.');
    $check(Config::get('absent.key', 'one') === 'one' && Config::get('absent.key', 'two') === 'two', 'Caller defaults lost.');

    if (in_array('--benchmark', $argv, true))
    {
        $names = array_keys($cached['config']);
        $iterations = 200;
        $normalStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++)
        {
            Config::clear();
            foreach ($names as $name) Config::get($name);
            $register = require ROOT . '/Config/routes.php';
            $register(new Router(new RouteCollection(), new Container()));
        }
        $normal = (hrtime(true) - $normalStart) / $iterations / 1e6;
        $cachedStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++)
        {
            Config::clear();
            $compiled = BootstrapCache::load($path);
            Config::prime($compiled['config']);
            foreach ($names as $name) Config::get($name);
        }
        $optimized = (hrtime(true) - $cachedStart) / $iterations / 1e6;
        printf("Config + routes, %d warm CLI iterations: normal %.3f ms; compiled %.3f ms.\n", $iterations, $normal, $optimized);
    }

    Env::set('DB_NAME', 'changed');
    $check(BootstrapCache::load($path) === null, 'Environment change reused stale configuration.');
    Env::set('DB_NAME', 'fixture');
    Env::set('APP_ENV', 'local');
    Env::set('REGISTRATION_ENABLED', true);
    Env::set('SQL_TOOL_ENABLED', true);
    $check(BootstrapCache::load($path) === null, 'Route flag change reused stale routes.');
    AtomicFile::writeIfChanged($path, BootstrapCache::compile(), 0600);
    $cached = BootstrapCache::load($path);
    $check($cached['routes']->allowedMethodsFor('/sql') === ['GET', 'POST'], 'Local SQL routes lost.');
    $check($cached['routes']->allowedMethodsFor('/inscription') === ['GET', 'POST'], 'Local registration routes lost.');
    $payload = require $path;
    $originalPayload = $payload;
    $payload['version'] = 2;
    AtomicFile::writeIfChanged($path, '<?php return ' . var_export($payload, true) . ';', 0600);
    $check(BootstrapCache::load($path) === null, 'Pre-migration controller namespaces reused from cache.');
    $payload = $originalPayload;
    $payload['validator'] = 'outdated';
    AtomicFile::writeIfChanged($path, '<?php return ' . var_export($payload, true) . ';', 0600);
    $check(BootstrapCache::load($path) === null, 'Outdated validation rules reused.');
    $payload = $originalPayload;
    $payload['routes'] = 'invalid';
    AtomicFile::writeIfChanged($path, '<?php return ' . var_export($payload, true) . ';', 0600);
    $check(BootstrapCache::load($path) === null, 'Corrupted route cache did not fall back.');
    file_put_contents($path, '<?php invalid syntax');
    $check(BootstrapCache::load($path) === null, 'Broken PHP artifact did not fall back.');
}
finally
{
    if (is_file($path)) unlink($path);
    rmdir($directory);
}
echo "PASS: compiled route equivalence, production gates, configuration defaults, environment invalidation and corrupt-cache fallback.\n";
