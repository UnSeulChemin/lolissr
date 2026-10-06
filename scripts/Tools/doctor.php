<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
$beforeBuild = in_array('--before-build', array_slice($argv, 1), true);
if (array_diff(array_slice($argv, 1), ['--before-build']) !== [])
{
    fwrite(STDERR, "Usage: composer doctor [-- --before-build]\n");
    exit(1);
}
$failures = 0;
function doctorCheck(string $name, callable $check, string $fix): void
{
    global $failures;
    try
    {
        $check();
        echo "[OK] $name\n";
    }
    catch (Throwable $error)
    {
        $failures++;
        // Never print connection exceptions or configuration values (credentials).
        echo "[FAIL] $name — $fix\n";
    }
}
function doctorRequire(bool $condition): void
{
    if (!$condition) throw new RuntimeException('Check failed.');
}
echo "LoliSSR doctor — diagnostic, aucune migration appliquee\n";
$composer = json_decode((string) file_get_contents(ROOT . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
doctorCheck('Version PHP >= 8.3 et < 9', static fn () => doctorRequire(PHP_VERSION_ID >= 80300 && PHP_VERSION_ID < 90000), 'Installer PHP 8.x >= 8.3 compatible avec Composer.');
foreach (array_keys($composer['require']) as $dependency)
{
    if (!str_starts_with($dependency, 'ext-')) continue;
    $extension = substr($dependency, 4);
    doctorCheck('Extension ' . $extension, static fn () => doctorRequire(extension_loaded($extension)), 'Activer cette extension dans le php.ini du CLI.');
}
doctorCheck('Autoload Composer', static fn () => doctorRequire(is_file(ROOT . '/vendor/autoload.php')), 'Executer composer install.');
if (!is_file(ROOT . '/vendor/autoload.php')) exit(1);
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
$configured = false;
doctorCheck('Configuration .env', static function () use (&$configured): void
{
    doctorRequire(is_file(ROOT . '/.env'));
    \Framework\Application\Bootstrap::loadEnvOnly();
    $configured = true;
}, 'Verifier .env avec les options de .env.example, sans publier ses secrets.');
if ($beforeBuild)
{
    doctorCheck('Preflight sur environnement de developpement', static fn () => doctorRequire($configured && !\Framework\Config\ApplicationConfig::isProduction()), 'Executer composer preflight sur le poste de developpement, avant le deploiement.');
}
foreach (['storage/cache', 'storage/logs', 'storage/sessions', 'storage/backups', 'public/images'] as $directory)
{
    doctorCheck('Ecriture ' . $directory, static fn () => doctorRequire(is_dir(ROOT . '/' . $directory) && is_writable(ROOT . '/' . $directory)), 'Creer le dossier et donner les droits au compte PHP/Apache.');
}
if (!\Framework\Config\ApplicationConfig::isProduction())
{
    require_once ROOT . '/scripts/Assets/JavaScript/Support/JavaScriptBuilder.php';
    doctorCheck('esbuild', static fn () => doctorRequire(is_file(JavaScriptBuilder::binary(ROOT))), 'Executer composer js:install.');
    doctorCheck('Dependances de verification', static fn () => doctorRequire(extension_loaded('pdo_sqlite') && is_file(ROOT . '/vendor/phpstan/phpstan/phpstan.phar')), 'Executer composer install et activer pdo_sqlite.');
}
if (!$beforeBuild)
{
    doctorCheck('Versions des assets', static function (): void
    {
        $assets = require ROOT . '/Config/assets.php';
        doctorRequire(is_array($assets) && $assets !== []);
        foreach ($assets as $path => $hash)
            doctorRequire(is_file(ROOT . '/public/' . $path) && hash_file('sha256', ROOT . '/public/' . $path) === $hash);
    }, 'Executer composer assets:build avant de deployer tous les fichiers et manifestes.');
    doctorCheck('Bundle JavaScript et sources', static function (): void
    {
        $manifest = require ROOT . '/Config/javascript.php';
        $sources = require ROOT . '/scripts/Assets/JavaScript/javascript-sources.php';
        doctorRequire(isset($manifest['entry'], $manifest['preloads'], $manifest['files'], $sources['sources'], $sources['manifest_hash']));
        doctorRequire($manifest['files'] !== [] && hash('sha256', serialize($manifest)) === $sources['manifest_hash']);
        foreach ($manifest['files'] as $file) doctorRequire(is_file(ROOT . '/public/' . $file));
        foreach ([$manifest['entry'], ...$manifest['preloads']] as $file) doctorRequire(in_array($file, $manifest['files'], true));
        foreach ($sources['sources'] as $file => $hash) doctorRequire(is_file(ROOT . '/' . $file) && hash_file('sha256', ROOT . '/' . $file) === $hash);
    }, 'Executer composer assets:build puis deployer les sources, chunks et manifestes ensemble.');
}
if ($configured)
{
    doctorCheck('Connexion MySQL et migrations', static function (): void
    {
        require_once ROOT . '/scripts/Database/Support/MigrationRunner.php';
        $database = new \Framework\Database\Database();
        $database->exec('SET SESSION TRANSACTION READ ONLY');
        ob_start();
        try
        {
            (new MigrationRunner($database, ROOT . '/scripts/Database/migrations'))->run('status');
            $status = (string) ob_get_contents();
        }
        finally
        { ob_end_clean(); }
        doctorRequire(!preg_match('/:\s*(pending|running|failed)\b/', $status));
    }, 'Verifier MySQL et composer db:migrate:status ; traiter les migrations avant publication.');
    doctorCheck('Acces HTTP (localhost, sans authentification)', static function (): void
    {
        $curl = curl_init('http://localhost' . \Framework\Config\ApplicationConfig::baseUri());
        doctorRequire($curl !== false);
        try
        {
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_FOLLOWLOCATION => false]);
            $body = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            doctorRequire(is_string($body) && (($status === 200 && $body !== '') || in_array($status, [301, 302, 303, 307, 308], true)));
        }
        finally
        { curl_close($curl); }
    }, 'Demarrer Apache et verifier APP_BASE_URI et le virtual host localhost.');
}
echo $failures === 0 ? "Diagnostic OK.\n" : "Diagnostic: $failures probleme(s).\n";
exit($failures === 0 ? 0 : 1);
