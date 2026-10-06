<?php
declare(strict_types=1);
namespace App\Support\Media;

// Test-only fault injection; ordinary image writes keep their native behavior.
function file_put_contents(string $path, string $contents, int $flags = 0): int|false
{
    return \file_put_contents($path, str_starts_with(basename($path), '.short-manifest-') ? substr($contents, 0, 3) : $contents, $flags);
}
