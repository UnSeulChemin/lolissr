<?php
declare(strict_types=1);
namespace App\Services\Admin;
use RuntimeException;
final class RecommendationJob extends AdminJob
{
    protected static function name(): string
    { return 'recommendations'; }
    public static function start(?int $ownerId = null): void
    {
        if ($ownerId !== null && $ownerId < 1) throw new RuntimeException('Compte invalide.');
        static::launch($ownerId !== null ? [(string) $ownerId] : []);
    }
}