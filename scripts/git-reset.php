<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{
    http_response_code(403);
    exit;
}

$root = dirname(__DIR__);

// Discard tracked changes, then remove untracked files and directories.
// Ignored files are preserved. Stop immediately if either command fails.
foreach ([['git', 'reset', '--hard', 'HEAD'], ['git', 'clean', '-fd']] as $command)
{
    echo implode(' ', $command) . PHP_EOL;

    $process = proc_open(
        $command,
        [0 => STDIN, 1 => STDOUT, 2 => STDERR],
        $pipes,
        $root,
        null,
        ['bypass_shell' => true]
    );

    if (! is_resource($process))
    {
        fwrite(STDERR, 'Unable to start Git.' . PHP_EOL);
        exit(1);
    }

    $status = proc_close($process);
    if ($status !== 0)
    {
        exit($status > 0 ? $status : 1);
    }
}
