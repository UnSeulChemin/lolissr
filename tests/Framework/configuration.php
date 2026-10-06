<?php

declare(strict_types=1);

if (($argv[1] ?? '') === 'bootstrap-child')
{
    define('ROOT', $argv[2]);
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
    require dirname(__DIR__, 2) . '/Framework/Support/Helpers.php';
    ini_set('display_errors', '1');
    ini_set('error_log', ROOT . '/error.log');
    register_shutdown_function(static function (): void
    {
        file_put_contents(ROOT . '/status', (string) http_response_code());
    });
    Framework\Application\Bootstrap::run();
}

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Config\Config;
use Framework\Config\DatabaseConfig;
use Framework\Config\Environment;
use Framework\Config\EnvironmentValidator;

$check = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};
$directory = sys_get_temp_dir() . '/framework-config-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$path = $directory . '/.env';
try
{
    Environment::load(ROOT . '/.env.example');
    Environment::set('DB_NAME', 'fixture');
    Environment::set('DB_USER', 'fixture');
    foreach (['APP_PAGINATION', 'DB_PORT', 'DB_SLOW_QUERY_THRESHOLD', 'UPLOAD_MAX_SIZE',
        'UPLOAD_MAX_WIDTH', 'UPLOAD_MAX_HEIGHT', 'UPLOAD_MAX_PIXELS', 'CACHE_TTL', 'LOG_RETENTION_DAYS'] as $key)
    {
        $original = Environment::get($key);
        foreach ([true, false, 1.0, null, 'true', '1.5', '0', '-1'] as $invalid)
        {
            Environment::set($key, $invalid);
            try
            {
                EnvironmentValidator::validate();
                throw new LogicException('Invalid integer accepted: ' . $key);
            }
            catch (RuntimeException)
            {}
        }
        foreach ([1, '42'] as $valid)
        {
            Environment::set($key, $valid);
            EnvironmentValidator::validate();
            $check(Environment::int($key) === (int) $valid, 'Valid integer rejected: ' . $key);
        }
        Environment::set($key, $original);
    }
    foreach ([true, false, 1.0, null, 'true', '1.5'] as $invalid)
    {
        Environment::set('AUDIT_VALUE', $invalid);
        $check(Environment::int('AUDIT_VALUE', 7) === 7, 'Integer helper accepted an invalid type.');
    }
    Environment::clear();

    foreach (['true' => true, 'false' => false, '(false)' => false, 'null' => null, 'empty' => '', '42' => '42'] as $raw => $expected)
    {
        file_put_contents($path, 'AUDIT_VALUE=' . $raw . "\n");
        Environment::load($path);
        $check(Environment::get('AUDIT_VALUE', 'fallback') === $expected, 'File conversion differs: ' . $raw);
        Environment::clear();
        putenv('AUDIT_VALUE=' . $raw);
        $check(Environment::get('AUDIT_VALUE', 'fallback') === $expected, 'System conversion differs: ' . $raw);
        Environment::clear();
        putenv('AUDIT_VALUE');
    }
    file_put_contents($path, "AUDIT_VALUE=\"false\"\nDB_PASS=\"  example  \"\n");
    Environment::load($path);
    Config::clear();
    $check(Environment::get('AUDIT_VALUE') === 'false', 'Quoted literal converted.');
    $check(DatabaseConfig::pass() === '  example  ', 'Quoted password trimmed.');
    Environment::clear();
    putenv('DB_PASS=  system password  ');
    Config::clear();
    $check(DatabaseConfig::pass() === '  system password  ', 'System password trimmed.');
    putenv('DB_PASS');

    Environment::clear();
    putenv('AUDIT_VALUE=process-original');
    $_ENV['AUDIT_VALUE'] = null;
    $_SERVER['AUDIT_VALUE'] = 'server-original';
    file_put_contents($path, "AUDIT_VALUE=file\n");
    Environment::load($path);
    Environment::set('AUDIT_VALUE', 'second');
    Environment::set('AUDIT_VALUE', null);
    $check(Environment::get('AUDIT_VALUE', 'fallback') === null, 'Explicit null lost.');
    file_put_contents($path, '');
    Environment::load($path);
    $check(getenv('AUDIT_VALUE') === 'process-original', 'Process environment not restored.');
    $check(array_key_exists('AUDIT_VALUE', $_ENV) && $_ENV['AUDIT_VALUE'] === null, 'Original ENV null lost.');
    $check($_SERVER['AUDIT_VALUE'] === 'server-original', 'Original SERVER value lost.');
    $check(Environment::get('AUDIT_VALUE') === 'server-original', 'Restored lookup precedence changed.');
    Environment::clear();
    unset($_ENV['AUDIT_VALUE'], $_SERVER['AUDIT_VALUE']);
    putenv('AUDIT_VALUE');
    Environment::set('AUDIT_VALUE', 'temporary');
    Environment::clear();
    $check(! Environment::has('AUDIT_VALUE'), 'Originally absent variable retained.');
    putenv('AUDIT_VALUE=');
    Environment::set('AUDIT_VALUE', 'temporary');
    Environment::clear();
    $check(getenv('AUDIT_VALUE') === '', 'Original empty process value lost.');
    putenv('AUDIT_VALUE');

    foreach (['"', "'", '"unfinished', "'unfinished", "\"mismatched'"] as $raw)
    {
        file_put_contents($path, "# comment\n\nAUDIT_VALUE=" . $raw . "\n");
        try
        {
            Environment::load($path);
            throw new LogicException('Unclosed quote accepted.');
        }
        catch (RuntimeException $error)
        {
            $check(str_contains($error->getMessage(), 'line 3'), 'Quoted error line lost.');
            $check(! str_contains($error->getMessage(), 'unfinished'), 'Quoted value exposed.');
        }
    }
    foreach (['""' => '', "''" => '', '"false"' => 'false', "' spaced '" => ' spaced ', "O'Reilly" => "O'Reilly"] as $raw => $expected)
    {
        file_put_contents($path, 'AUDIT_VALUE=' . $raw . "\n");
        Environment::load($path);
        $check(Environment::get('AUDIT_VALUE') === $expected, 'Valid literal changed.');
    }

    file_put_contents($path, "\n# comment\n\nINVALID_DECLARATION\n");
    try
    {
        Environment::load($path);
        throw new LogicException('Malformed declaration accepted.');
    }
    catch (RuntimeException $error)
    {
        $check(str_contains($error->getMessage(), 'line 4'), 'Physical line number lost.');
    }

    $process = proc_open([PHP_BINARY, __FILE__, 'bootstrap-child', $directory], [
        0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']
    ], $pipes);
    $check(is_resource($process), 'Cannot run bootstrap fixture.');
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $check(proc_close($process) === 0 && $error === '', 'Bootstrap fixture failed: ' . $error);
    $check($output === 'Une erreur interne est survenue.', 'Bootstrap exposed configuration details.');
    $check(file_get_contents($directory . '/status') === '500', 'Bootstrap did not return 500.');
    $check(str_contains(file_get_contents($directory . '/error.log'), 'line 4'), 'Bootstrap failure not logged.');
}
finally
{
    Environment::clear();
    unset($_ENV['AUDIT_VALUE'], $_SERVER['AUDIT_VALUE']);
    putenv('AUDIT_VALUE');
    putenv('DB_PASS');
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($path)) unlink($path);
    rmdir($directory);
}
echo "PASS: environment types, restoration, quoted literals, physical line numbers and early bootstrap failures.\n";
