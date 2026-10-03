<?php
declare(strict_types=1);

final class BuildLock
{
    /** @var resource|null */
    private static $handle = null;

    public static function acquire(string $root): void
    {
        if (self::$handle !== null) return;
        $directory = $root . '/storage';
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Cannot create build lock directory.');
        $handle = fopen($directory . '/.build.lock', 'c');
        if ($handle === false) throw new RuntimeException('Cannot open build lock.');
        if (!flock($handle, LOCK_EX | LOCK_NB))
        {
            fclose($handle);
            throw new RuntimeException('Another build or asset cleanup is running. Retry when it finishes.');
        }
        self::$handle = $handle;
        register_shutdown_function(static function (): void
        {
            flock(self::$handle, LOCK_UN);
            fclose(self::$handle);
            self::$handle = null;
        });
    }
}
