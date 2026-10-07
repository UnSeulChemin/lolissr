<?php
declare(strict_types=1);
namespace App\Services\Admin;
use RuntimeException;
final class MaintenanceJob extends AdminJob
{
    protected static function name(): string
    { return 'maintenance'; }
    public static function start(string $task, ?int $ownerId = null): void
    {
        if (!in_array($task, ['doctor', 'assets', 'js-prune', 'js-prune-force', 'images-check', 'images', 'cache', 'reset', 'backup', 'xp-check', 'xp-apply', 'migrations-create', 'migrations-check', 'migrations'], true)) throw new RuntimeException('Commande invalide.');
        if ($ownerId !== null && ($ownerId < 1 || !in_array($task, ['xp-check', 'xp-apply'], true))) throw new RuntimeException('Compte invalide.');
        static::launch([$task, ...($ownerId !== null ? [(string) $ownerId] : [])]);
    }
}