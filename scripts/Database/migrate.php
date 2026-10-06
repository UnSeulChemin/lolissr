<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';
require ROOT . '/scripts/Support/MigrationRunner.php';

try
{
    $action = $argv[1] ?? 'status';
    if (!in_array($action, ['status', 'apply', 'baseline'], true)
        || count($argv) !== ($action === 'baseline' ? 3 : (isset($argv[1]) ? 2 : 1)))
        throw new InvalidArgumentException('Usage: migrate.php status|apply|baseline FILE.sql');
    \Framework\Application\Bootstrap::loadEnvOnly();
    $runner = new MigrationRunner(new \Framework\Database\Database(), ROOT . '/scripts/Database/migrations');
    $runner->run($action, $argv[2] ?? null);
}
catch (Throwable $error)
{
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
