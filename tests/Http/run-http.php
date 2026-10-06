<?php

declare(strict_types=1);

$repeat = 1;
foreach (array_slice($argv, 1) as $argument)
{
    if (!preg_match('/^--repeat=([1-9][0-9]?)$/', $argument, $matches) || (int) $matches[1] > 20)
    {
        fwrite(STDERR, "Usage: php tests/Http/run-http.php [--repeat=1..20]\n");
        exit(1);
    }
    $repeat = (int) $matches[1];
}
$bootstrap = require __DIR__ . '/bootstrap-runner.php';

$base = (string) ($bootstrap['base'] ?? '');

/** @var list<array<string, mixed>> $tests */
$tests = is_array($bootstrap['tests'] ?? null)
    ? array_values($bootstrap['tests'])
    : [];

$runner = new HttpTestRunner(base: $base, tests: $tests, stats: new HttpTestStatistics(), repeat: $repeat);

exit($runner->run());
