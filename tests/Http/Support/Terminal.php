<?php

declare(strict_types=1);

function ok(string $label): void
{
    echo "✅ {$label}" . PHP_EOL;
}

function fail(
    string $label,
    string $reason,
): void {
    echo "❌ {$label} ({$reason})" . PHP_EOL;
}