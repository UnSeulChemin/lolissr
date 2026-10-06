<?php

declare(strict_types=1);

use Framework\Application\BootstrapCache;
use Framework\Config\Config;
use Framework\Config\Env;

$project = dirname(__DIR__, 2);
$directory = sys_get_temp_dir() . '/config-files-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
define('ROOT', $directory);
require $project . '/vendor/autoload.php';
require $project . '/Framework/Support/Helpers.php';

$check = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};
mkdir($directory . '/Config');
mkdir($directory . '/Config/routes');
mkdir($directory . '/Framework');
mkdir($directory . '/Framework/Config');
try
{
    copy($project . '/Framework/Config/EnvironmentValidator.php', $directory . '/Framework/Config/EnvironmentValidator.php');
    file_put_contents($directory . '/Config/routes/web.php', '<?php return static function ($router): void {};');
    Env::load($project . '/.env.example');
    Env::set('DB_NAME', 'fixture');
    Env::set('DB_USER', 'fixture');

    foreach (['missing', 'missing.key'] as $key)
    {
        $check(Config::get($key, 'one') === 'one', 'Missing config default lost.');
        $check(Config::get($key, 'two') === 'two', 'Missing config cached caller default.');
    }
    file_put_contents($directory . '/Config/empty.php', '<?php return [];');
    file_put_contents($directory . '/Config/values.php', '<?php return ["null" => null, "false" => false, "zero" => 0];');
    $check(Config::get('empty', 'fallback') === [], 'Empty config confused with missing file.');
    foreach (['null' => null, 'false' => false, 'zero' => 0] as $key => $expected)
    {
        $check(Config::get('values.' . $key, 'fallback') === $expected, 'Existing value replaced with default.');
    }

    foreach (['"invalid"', 'null', 'false', '42'] as $value)
    {
        file_put_contents($directory . '/Config/broken.php', '<?php return ' . $value . ';');
        Config::clear();
        foreach ([static fn () => Config::get('broken'), static fn () => BootstrapCache::compile()] as $operation)
        {
            try
            {
                $operation();
                throw new LogicException('Invalid configuration accepted.');
            }
            catch (RuntimeException $error)
            {
                $check($error->getMessage() === 'Configuration must return an array: broken', 'Unexpected config error.');
            }
        }
    }
    unlink($directory . '/Config/broken.php');
    file_put_contents($directory . '/compiled.php', BootstrapCache::compile());
    $cached = BootstrapCache::load($directory . '/compiled.php');
    $check($cached !== null, 'Valid configuration did not compile.');
    Config::prime($cached['config']);
    $check(Config::get('empty', 'fallback') === [], 'Primed empty array lost.');
    $check(Config::get('values.null', 'fallback') === null, 'Primed null lost.');
    $check(Config::get('missing', 'fallback') === 'fallback', 'Primed missing default lost.');
}
finally
{
    Config::clear();
    Env::clear();
    foreach (glob($directory . '/Config/*.php') ?: [] as $file) unlink($file);
    if (is_file($directory . '/compiled.php')) unlink($directory . '/compiled.php');
    unlink($directory . '/Framework/Config/EnvironmentValidator.php');
    rmdir($directory . '/Framework/Config');
    rmdir($directory . '/Framework');
    unlink($directory . '/Config/routes/web.php');
    rmdir($directory . '/Config/routes');
    rmdir($directory . '/Config');
    rmdir($directory);
}
echo "PASS: configuration defaults, empty arrays, null values and invalid-file rejection at load and compile.\n";
