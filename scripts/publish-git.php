<?php

declare(strict_types=1);

use Framework\Application\Bootstrap;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/helpers.php';

Bootstrap::loadEnvOnly();

$projectName = trim((string) env('APP_NAME', 'LoliSSR'));
$version = trim((string) env('APP_VERSION', '0.0.0'));

if ($projectName === '')
{
    fail('APP_NAME is required.');
}

if ($version === '')
{
    fail('APP_VERSION is required.');
}

$commitMessage = "chore: {$projectName} v{$version}";

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '              >> LOLISSR ADVENTURER GUILD <<' . PHP_EOL;
echo PHP_EOL;
echo '                   Quest : Publish Git' . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

runCommand(
    ['git', '--version'],
    'Git could not be found.',
    false
);

runCommand(
    ['git', 'rev-parse', '--is-inside-work-tree'],
    'The current directory is not a Git repository.',
    false
);

echo '[SYSTEM]' . PHP_EOL;
echo 'Preparing Git publication...' . PHP_EOL;
echo 'Application    : ' . $projectName . PHP_EOL;
echo 'Version        : ' . $version . PHP_EOL;
echo 'Commit message : ' . $commitMessage . PHP_EOL;
echo PHP_EOL;

echo '[SYSTEM]' . PHP_EOL;
echo 'Staging files...' . PHP_EOL;

runCommand(
    ['git', 'add', '.'],
    'Git could not stage the files.'
);

if (! hasStagedChanges())
{
    fail('No staged changes were found.');
}

echo PHP_EOL;
echo '[SYSTEM]' . PHP_EOL;
echo 'Creating commit...' . PHP_EOL;

runCommand(
    ['git', 'commit', '-m', $commitMessage],
    'Git could not create the commit.'
);

echo PHP_EOL;
echo '[SYSTEM]' . PHP_EOL;
echo 'Pushing commit to origin/master...' . PHP_EOL;

runCommand(
    ['git', 'push', 'origin', 'master'],
    'The commit could not reach origin/master.'
);

echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;
echo '                 QUEST COMPLETED' . PHP_EOL;
echo PHP_EOL;
echo '        The changes have reached the Git kingdom.' . PHP_EOL;
echo PHP_EOL;
echo '        Commit:' . PHP_EOL;
echo '        ' . $commitMessage . PHP_EOL;
echo PHP_EOL;
echo '============================================================' . PHP_EOL;
echo PHP_EOL;

exit(0);

function hasStagedChanges(): bool
{
    $process = proc_open(
        ['git', 'diff', '--cached', '--quiet'],
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
        fail('Unable to inspect staged changes.');
    }

    $exitCode = proc_close($process);

    return match ($exitCode)
    {
        0 => false,
        1 => true,
        default => fail('Unable to inspect staged changes.'),
    };
}

/**
 * @param list<string> $command
 */
function runCommand(array $command, string $failureMessage, bool $displayOutput = true): void
{
    $nullDevice = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

    $descriptors = $displayOutput
        ? [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ]
        : [
            0 => STDIN,
            1 => ['file', $nullDevice, 'w'],
            2 => ['file', $nullDevice, 'w'],
        ];

    $process = proc_open($command, $descriptors, $pipes, ROOT);

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