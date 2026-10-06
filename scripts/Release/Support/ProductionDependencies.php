<?php

declare(strict_types=1);

final class ProductionDependencies
{
    public static function install(string $directory): void
    {
        // Run in staging only: keep the developer's vendor directory intact.
        $process = proc_open(
            'composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts --no-plugins',
            [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR],
            $pipes,
            $directory
        );
        if (!is_resource($process)) throw new RuntimeException('Cannot start Composer for release dependencies.');
        fclose($pipes[0]);
        if (proc_close($process) !== 0) throw new RuntimeException('Production dependency installation failed.');
    }
}
