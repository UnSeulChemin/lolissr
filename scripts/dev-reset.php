<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '              >> LOLISSR ADVENTURER GUILD <<' . PHP_EOL;
echo PHP_EOL;
echo '                Quest : Development Reset' . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

runPhpScript('clear-runtime.php', ['cache'], 'Clearing cache...');
runPhpScript('clear-runtime.php', ['sessions'], 'Clearing sessions...');
runPhpScript('clear-runtime.php', ['logs'], 'Clearing logs...');

echo PHP_EOL;
echo '[SYSTEM]' . PHP_EOL;
echo 'Regenerating Composer autoload...' . PHP_EOL;

runCommand(
    ['composer', 'dump-autoload'],
    'Composer autoload could not be regenerated.'
);

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '                 QUEST COMPLETED' . PHP_EOL;
echo PHP_EOL;
echo '     Cache cleared.' . PHP_EOL;
echo '     Sessions cleared.' . PHP_EOL;
echo '     Logs cleared.' . PHP_EOL;
echo '     Autoload regenerated.' . PHP_EOL;
echo PHP_EOL;
echo '     The development environment is refreshed.' . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

exit(0);

/**
 * @param list<string> $arguments
 */
function runPhpScript(string $script, array $arguments, string $message): void
{
    echo PHP_EOL;
    echo '[SYSTEM]' . PHP_EOL;
    echo $message . PHP_EOL;

    runCommand(
        array_merge(
            [PHP_BINARY, ROOT . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $script],
            $arguments
        ),
        $message . ' Failed.'
    );
}

/**
 * @param list<string> $command
 */
function runCommand(array $command, string $failureMessage): void
{
    $process = proc_open(
        $command,
        [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ],
        $pipes,
        ROOT
    );

    if (! is_resource($process))
    {
        fail($failureMessage);
    }

    $exitCode = proc_close($process);

    if ($exitCode !== 0)
    {
        fail($failureMessage);
    }
}

function fail(string $message): never
{
    fwrite(STDERR, PHP_EOL . '[FAILED] ' . $message . PHP_EOL);

    exit(1);
}