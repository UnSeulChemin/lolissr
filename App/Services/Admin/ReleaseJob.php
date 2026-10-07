<?php
declare(strict_types=1);
namespace App\Services\Admin;
use RuntimeException;
final class ReleaseJob extends AdminJob
{
    protected static function name(): string
    { return 'releases'; }
    public static function start(?int $ownerId = null): void
    {
        if ($ownerId !== null && $ownerId < 1) throw new RuntimeException('Compte invalide.');
        static::launch($ownerId !== null ? [(string) $ownerId] : []);
    }
}