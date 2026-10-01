<?php

declare(strict_types=1);

$bootstrap = require __DIR__ . '/bootstrap-runner.php';

$base = (string) ($bootstrap['base'] ?? '');

/** @var list<array<string, mixed>> $tests */
$tests = is_array($bootstrap['tests'] ?? null)
    ? array_values($bootstrap['tests'])
    : [];

$runner = new HttpTestRunner(
    base: $base,
    tests: $tests,
    stats: new Stats()
);

exit($runner->run());