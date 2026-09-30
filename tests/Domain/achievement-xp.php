<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Profile\AchievementXpService;
use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';
Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$database = $container->get(Database::class);
// Connection-local temporary tables shadow the real tables. No account data is changed.
$database->exec('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, level INT NOT NULL, xp INT NOT NULL) ENGINE=InnoDB');
$database->exec('CREATE TEMPORARY TABLE achievement_xp_rewards (user_id INT NOT NULL, achievement_key VARCHAR(100) NOT NULL, xp INT NOT NULL, UNIQUE KEY (user_id, achievement_key)) ENGINE=InnoDB');
$database->exec('INSERT INTO users VALUES (1, 1, 0), (2, 1, 0)');
$service = $container->get(AchievementXpService::class);
$user = new User();
$user->id = 1;
$user->level = 1;
$user->xp = 0;
$assert = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};
$service->rewardManga($user, 0, 0);
$assert($service->totalForUser($user) === 0, 'Zero count granted XP');
$service->rewardManga($user, 10, 0);
$assert($service->totalForUser($user) === 550, 'Initial batch incorrect');
$service->rewardManga($user, 200, 0);
$assert($service->totalForUser($user) === 19300, 'Partial batch incorrect');
$before = [$user->level, $user->xp];
$service->rewardManga($user, 200, 0);
$assert([$user->level, $user->xp] === $before, 'Duplicate reward changed level');
$assert((int)$database->query('SELECT COUNT(*) FROM achievement_xp_rewards')->fetchColumn() === 6, 'Duplicate reward inserted');
$assert((($user->level - 1) * $user->level / 2 * 5 + $user->xp) === 19300, 'Level progression incorrect');
try
{
    $database->transaction(function () use ($service, $user): void {
        $service->rewardManga($user, 200, 50);
        throw new RuntimeException('rollback fixture');
    });
}
catch (RuntimeException $error)
{
    if ($error->getMessage() !== 'rollback fixture') throw $error;
}
$assert([$user->level, $user->xp] === $before, 'Rollback did not restore in-memory level');
$assert($service->totalForUser($user) === 19300, 'Rollback persisted rewards');
$stored = $database->query('SELECT level, xp FROM users WHERE id = 1')->fetch();
$assert([(int)$stored->level, (int)$stored->xp] === $before, 'Rollback persisted level');
$other = new User();
$other->id = 2;
$other->level = 1;
$other->xp = 0;
$service->rewardManga($other, 1, 0);
$assert($service->totalForUser($other) === 50 && $service->totalForUser($user) === 19300, 'User reward isolation failed');
echo "PASS: achievement batches, partial claims, duplicate prevention, level progression, rollback and user isolation (temporary tables only).\n";
