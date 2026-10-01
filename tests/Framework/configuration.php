<?php

declare(strict_types=1);

if (($argv[1] ?? '') === 'bootstrap-child')
{
    define('ROOT', $argv[2]);
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
    require dirname(__DIR__, 2) . '/Framework/Support/Helpers.php';
    ini_set('display_errors', '1');
    ini_set('error_log', ROOT . '/error.log');
    register_shutdown_function(static function (): void {
        file_put_contents(ROOT . '/status', (string) http_response_code());
    });
    Framework\Application\Bootstrap::run();
}

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';

use Framework\Config\Config;
use Framework\Config\DatabaseConfig;
use Framework\Config\Env;
use Framework\Config\EnvironmentValidator;

$check = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
$directory = sys_get_temp_dir() . '/framework-config-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$path = $directory . '/.env';
try
{
    Env::load(ROOT . '/.env.example');
    Env::set('DB_NAME', 'fixture');
    Env::set('DB_USER', 'fixture');
    foreach (['APP_PAGINATION', 'DB_PORT', 'DB_SLOW_QUERY_THRESHOLD', 'UPLOAD_MAX_SIZE',
        'UPLOAD_MAX_WIDTH', 'UPLOAD_MAX_HEIGHT', 'UPLOAD_MAX_PIXELS', 'CACHE_TTL', 'LOG_RETENTION_DAYS'] as $key)
    {
        $original = Env::get($key);
        foreach ([true, false, 1.0, null, 'true', '1.5', '0', '-1'] as $invalid)
        {
            Env::set($key, $invalid);
            try
            {
                EnvironmentValidator::validate();
                throw new LogicException('Invalid integer accepted: ' . $key);
            }
            catch (RuntimeException) {}
        }
        foreach ([1, '42'] as $valid)
        {
            Env::set($key, $valid);
            EnvironmentValidator::validate();
            $check(Env::int($key) === (int) $valid, 'Valid integer rejected: ' . $key);
        }
        Env::set($key, $original);
    }
    foreach ([true, false, 1.0, null, 'true', '1.5'] as $invalid)
    {
        Env::set('AUDIT_VALUE', $invalid);
        $check(Env::int('AUDIT_VALUE', 7) === 7, 'Integer helper accepted an invalid type.');
    }
    Env::clear();

    foreach (['true' => true, 'false' => false, '(false)' => false, 'null' => null, 'empty' => '', '42' => '42'] as $raw => $expected)
    {
        file_put_contents($path, 'AUDIT_VALUE=' . $raw . "\n");
        Env::load($path);
        $check(Env::get('AUDIT_VALUE', 'fallback') === $expected, 'File conversion differs: ' . $raw);
        Env::clear();
        putenv('AUDIT_VALUE=' . $raw);
        $check(Env::get('AUDIT_VALUE', 'fallback') === $expected, 'System conversion differs: ' . $raw);
        Env::clear();
        putenv('AUDIT_VALUE');
    }
    file_put_contents($path, "AUDIT_VALUE=\"false\"\nDB_PASS=\"  example  \"\n");
    Env::load($path);
    Config::clear();
    $check(Env::get('AUDIT_VALUE') === 'false', 'Quoted literal converted.');
    $check(DatabaseConfig::pass() === '  example  ', 'Quoted password trimmed.');
    Env::clear();
    putenv('DB_PASS=  system password  ');
    Config::clear();
    $check(DatabaseConfig::pass() === '  system password  ', 'System password trimmed.');
    putenv('DB_PASS');

    Env::clear();
    putenv('AUDIT_VALUE=process-original');
    $_ENV['AUDIT_VALUE'] = null;
    $_SERVER['AUDIT_VALUE'] = 'server-original';
    file_put_contents($path, "AUDIT_VALUE=file\n");
    Env::load($path);
    Env::set('AUDIT_VALUE', 'second');
    Env::set('AUDIT_VALUE', null);
    $check(Env::get('AUDIT_VALUE', 'fallback') === null, 'Explicit null lost.');
    file_put_contents($path, '');
    Env::load($path);
    $check(getenv('AUDIT_VALUE') === 'process-original', 'Process environment not restored.');
    $check(array_key_exists('AUDIT_VALUE', $_ENV) && $_ENV['AUDIT_VALUE'] === null, 'Original ENV null lost.');
    $check($_SERVER['AUDIT_VALUE'] === 'server-original', 'Original SERVER value lost.');
    $check(Env::get('AUDIT_VALUE') === 'server-original', 'Restored lookup precedence changed.');
    Env::clear();
    unset($_ENV['AUDIT_VALUE'], $_SERVER['AUDIT_VALUE']);
    putenv('AUDIT_VALUE');
    Env::set('AUDIT_VALUE', 'temporary');
    Env::clear();
    $check(! Env::has('AUDIT_VALUE'), 'Originally absent variable retained.');
    putenv('AUDIT_VALUE=');
    Env::set('AUDIT_VALUE', 'temporary');
    Env::clear();
    $check(getenv('AUDIT_VALUE') === '', 'Original empty process value lost.');
    putenv('AUDIT_VALUE');

    foreach (['"', "'", '"unfinished', "'unfinished", "\"mismatched'"] as $raw)
    {
        file_put_contents($path, "# comment\n\nAUDIT_VALUE=" . $raw . "\n");
        try
        {
            Env::load($path);
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
        Env::load($path);
        $check(Env::get('AUDIT_VALUE') === $expected, 'Valid literal changed.');
    }

    file_put_contents($path, "\n# comment\n\nINVALID_DECLARATION\n");
    try
    {
        Env::load($path);
        throw new LogicException('Malformed declaration accepted.');
    }
    catch (RuntimeException $error)
    {
        $check(str_contains($error->getMessage(), 'line 4'), 'Physical line number lost.');
    }

    $process = proc_open([PHP_BINARY, __FILE__, 'bootstrap-child', $directory], [
        0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
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
    Env::clear();
    unset($_ENV['AUDIT_VALUE'], $_SERVER['AUDIT_VALUE']);
    putenv('AUDIT_VALUE');
    putenv('DB_PASS');
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($path)) unlink($path);
    rmdir($directory);
}
echo "PASS: environment types, restoration, quoted literals, physical line numbers and early bootstrap failures.\n";
