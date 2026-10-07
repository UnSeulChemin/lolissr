<?php

declare(strict_types=1);

use App\Models\User\User;
use App\Services\Profile\AchievementXpService;

use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$database = $container->get(Database::class);
// Connection-local temporary tables shadow the real tables. No account data is changed.
$database->exec(owned_fixture_sql($database, 'CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, level INT NOT NULL, xp INT NOT NULL) ENGINE=InnoDB'));
$database->exec(owned_fixture_sql($database, 'CREATE TEMPORARY TABLE achievement_xp_rewards (user_id INT NOT NULL, achievement_key VARCHAR(100) NOT NULL, xp INT NOT NULL, UNIQUE KEY (user_id, achievement_key)) ENGINE=InnoDB'));
$database->exec(owned_fixture_sql($database, 'INSERT INTO users VALUES (1, 1, 0), (2, 1, 0)'));
$service = $container->get(AchievementXpService::class);
$user = new User();
$user->id = 1;
$user->level = 1;
$user->xp = 0;
$assert = static function (bool $condition, string $message): void
{
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
    $database->transaction(function () use ($service, $user): void
    {
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
$database->exec("UPDATE achievement_xp_rewards SET xp = 999 WHERE user_id = 2 AND achievement_key = 'tomes_1'");
$database->exec(owned_fixture_sql($database, "INSERT INTO achievement_xp_rewards VALUES (2, 'tomes_25', 1250), (2, 'obsolete', 7)"));
$stats = new \App\DTO\Profile\Responses\ProfileStatsData(
    readTomes: 10, tomeXp: 50, completedSeries: 0, seriesXp: 0,
    readArtbooks: 0, artbookXp: 0, figurinesCollected: 0, figurinesXp: 0,
    nendoroidsCollected: 0, nendoroidsXp: 0, peluchesCollected: 0, peluchesXp: 0,
    vocabularyLearned: 0, vocabularyXp: 0, grammarLearned: 0, grammarXp: 0,
    totalXp: 2306, achievementXp: 2256
);
$beforeAudit = [$other->level, $other->xp, $service->totalForUser($other)];
$audit = $service->audit($other, $stats);
$assert($audit['missing'] === ['tomes_10' => 500], 'Audit missing rewards incorrect');
$assert(count($audit['issues']) === 4, 'Audit must detect amount, eligibility, unknown key and account mismatch');
$assert($audit['expectedTotal'] === 600 && $audit['expectedLevel'] === 16 && $audit['expectedXp'] === 0, 'Audit calculated progression incorrect');
$assert([$other->level, $other->xp, $service->totalForUser($other)] === $beforeAudit, 'Audit changed account data');
$service->rewardManga($other, $stats->readTomes, $stats->completedSeries);
$assert($service->totalForUser($other) === 2756, 'Apply must only add the missing reward');
$assert((int) $database->query("SELECT xp FROM achievement_xp_rewards WHERE user_id = 2 AND achievement_key = 'tomes_1'")->fetchColumn() === 999, 'Apply corrected an existing reward');
$afterApply = [$other->level, $other->xp, $service->totalForUser($other)];
$service->rewardManga($other, $stats->readTomes, $stats->completedSeries);
$assert([$other->level, $other->xp, $service->totalForUser($other)] === $afterApply, 'Repeated apply changed account data');
$service->reconcile($other, static fn () => $stats);
$assert($service->totalForUser($other) === 550 && [$other->level, $other->xp] === [16, 0], 'Reconciliation did not preserve base XP or correct rewards');
$assert((int) $database->query("SELECT xp FROM achievement_xp_rewards WHERE user_id = 2 AND achievement_key = 'tomes_1'")->fetchColumn() === 50, 'Wrong reward amount persisted');
$assert((int) $database->query("SELECT COUNT(*) FROM achievement_xp_rewards WHERE user_id = 2 AND achievement_key IN ('obsolete', 'tomes_25')")->fetchColumn() === 0, 'Unjustified rewards persisted');
$service->reconcile($other, static fn () => $stats);
$assert($service->totalForUser($other) === 550 && [$other->level, $other->xp] === [16, 0], 'Reconciliation is not idempotent');
$emptyStats = new \App\DTO\Profile\Responses\ProfileStatsData(
    readTomes: 0, tomeXp: 0, completedSeries: 0, seriesXp: 0,
    readArtbooks: 0, artbookXp: 0, figurinesCollected: 0, figurinesXp: 0,
    nendoroidsCollected: 0, nendoroidsXp: 0, peluchesCollected: 0, peluchesXp: 0,
    vocabularyLearned: 0, vocabularyXp: 0, grammarLearned: 0, grammarXp: 0,
    totalXp: 550, achievementXp: 550
);
try
{
    $service->reconcile($other, static function (): \App\DTO\Profile\Responses\ProfileStatsData
    { throw new RuntimeException('fixture failure'); });
    throw new RuntimeException('Expected reconciliation failure');
}
catch (RuntimeException $error)
{ if ($error->getMessage() !== 'fixture failure') throw $error; }
$assert($service->totalForUser($other) === 550 && [$other->level, $other->xp] === [16, 0], 'Failed reconciliation changed account');
$database->exec('ALTER TABLE users ADD CONSTRAINT fixture_reconcile_level CHECK (id <> 2 OR level > 1)');
try
{
    $service->reconcile($other, static fn () => $emptyStats);
    throw new RuntimeException('Expected update failure');
}
catch (PDOException)
{}
$assert($service->totalForUser($other) === 550 && [$other->level, $other->xp] === [16, 0], 'Failed level update did not restore rewards');
$database->exec('ALTER TABLE users DROP CHECK fixture_reconcile_level');
$service->reconcile($other, static fn () => $emptyStats);
$assert($service->totalForUser($other) === 0 && [$other->level, $other->xp] === [1, 0], 'Empty account did not return to level one');
$assert($service->totalForUser($user) === 19300, 'Reconciliation changed another account');
echo "PASS: achievement rewards, audit, reconciliation, duplicate prevention, rollback and user isolation (temporary tables only).\n";
