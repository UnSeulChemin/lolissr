<?php

declare(strict_types=1);

use App\Constants\AchievementRewards;
use App\Constants\UserXp;
use App\Models\Manga;
use App\Models\User;
use App\Services\Manga\MangaXpRewardService;
use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 2) . '/phpstan-bootstrap.php';
Bootstrap::loadEnvOnly();

function user(): ?User { return $GLOBALS['batchTestUser']; }
final class XpBatchQueryCounter extends PDOStatement
{
    public static array $queries = [];
    public function execute(?array $params = null): bool
    {
        self::$queries[] = $this->queryString;
        return parent::execute($params);
    }
}
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [XpBatchQueryCounter::class]);
// Connection-local tables shadow real data and disappear on disconnect.
$db->exec('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, level INT NOT NULL, xp INT NOT NULL) ENGINE=InnoDB');
$db->exec('CREATE TEMPORARY TABLE achievement_xp_rewards (user_id INT, achievement_key VARCHAR(100), xp INT, UNIQUE KEY (user_id, achievement_key)) ENGINE=InnoDB');
$db->exec('CREATE TEMPORARY TABLE manga (id INT PRIMARY KEY, slug VARCHAR(100), numero INT, statut VARCHAR(20), lu INT, xp_read_rewarded INT, xp_series_rewarded INT) ENGINE=InnoDB');
$db->exec('INSERT INTO users VALUES (1, 1, 0)');
$db->exec("INSERT INTO manga VALUES (1, 'alpha', 1, 'termine', 1, 0, 0)");
$user = new User();
$user->id = 1;
$GLOBALS['batchTestUser'] = $user;
$manga = new Manga();
$manga->id = 1;
$service = $container->get(MangaXpRewardService::class);
$assert = static function (bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
};
$earned = static fn (): int => (int) (($user->level - 1) * $user->level / 2 * 5 + $user->xp);
try
{
    $db->transaction(function () use ($service, $manga): void {
        $service->rewardRead($manga, 'alpha');
        throw new RuntimeException('initial rollback fixture');
    });
}
catch (RuntimeException $error)
{
    if ($error->getMessage() !== 'initial rollback fixture') throw $error;
}
$assert($earned() === 0 && (int) $db->query('SELECT COUNT(*) FROM achievement_xp_rewards')->fetchColumn() === 0,
    'Rollback persisted combined base/achievement rewards');
$assert((int) $db->query('SELECT xp_read_rewarded + xp_series_rewarded FROM manga WHERE id = 1')->fetchColumn() === 0,
    'Rollback persisted initial reward flags');
XpBatchQueryCounter::$queries = [];
$result = $db->transaction(fn () => $service->rewardRead($manga, 'alpha'));
$assert($result === ['xpEarned' => true, 'seriesXpEarned' => true], 'Missing base rewards');
$assert($earned() === UserXp::READ_TOME + UserXp::COMPLETE_SERIES + AchievementRewards::TOMES[1] + AchievementRewards::SERIES[1], 'Combined reward total changed');
$updates = array_filter(XpBatchQueryCounter::$queries, static fn ($sql) => preg_match('/UPDATE\s+users\b/i', $sql) === 1);
$locks = array_filter(XpBatchQueryCounter::$queries, static fn ($sql) => preg_match('/FROM\s+users\b[\s\S]*FOR UPDATE/i', $sql) === 1);
$assert(count($updates) === 1 && count($locks) === 1, 'XP batch did not use one user lock and update');
$before = [$user->level, $user->xp];
$result = $db->transaction(fn () => $service->rewardRead($manga, 'alpha'));
$assert($result === ['xpEarned' => false, 'seriesXpEarned' => false] && [$user->level, $user->xp] === $before, 'Duplicate reward');
$db->exec("INSERT INTO manga VALUES (2, 'beta', 1, 'termine', 1, 0, 0)");
$manga->id = 2;
try
{
    $db->transaction(function () use ($service, $manga): void {
        $service->rewardRead($manga, 'beta');
        throw new RuntimeException('rollback fixture');
    });
}
catch (RuntimeException $error)
{
    if ($error->getMessage() !== 'rollback fixture') throw $error;
}
$assert([$user->level, $user->xp] === $before, 'Rollback changed in-memory XP');
$stored = $db->query('SELECT level, xp FROM users WHERE id = 1')->fetch();
$assert([(int) $stored->level, (int) $stored->xp] === $before, 'Rollback persisted XP');
$assert((int) $db->query('SELECT xp_read_rewarded + xp_series_rewarded FROM manga WHERE id = 2')->fetchColumn() === 0, 'Rollback persisted reward flags');
$db->transaction(fn () => $service->rewardRead($manga, 'beta'));
$assert($earned() === UserXp::READ_TOME * 2 + UserXp::COMPLETE_SERIES * 2 + AchievementRewards::TOMES[1] + AchievementRewards::SERIES[1], 'Base rewards lost when achievements were already claimed');
echo "PASS: combined manga XP, one user lock/update, duplicate prevention and rollback (temporary tables only).\n";
