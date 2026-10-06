<?php

declare(strict_types=1);

use App\Constants\Profile\AchievementRewards;
use App\Constants\Profile\XpRewards;
use App\Models\User\User;

use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 3) . '/tests/Support/bootstrap.php';
Bootstrap::loadEnvOnly();

function user(): ?User
{ return $GLOBALS['collectionTestUser']; }
final class CollectionXpQueryCounter extends PDOStatement
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
$db->setAttribute(PDO::ATTR_STATEMENT_CLASS, [CollectionXpQueryCounter::class]);
// Temporary tables shadow real tables on this connection only.
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, level INT NOT NULL, xp INT NOT NULL) ENGINE=InnoDB'));
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE achievement_xp_rewards (user_id INT, achievement_key VARCHAR(100), xp INT, UNIQUE KEY (user_id, achievement_key)) ENGINE=InnoDB'));
$cases = [
    ['figurine', 'collect', 'collect_rewarded', 'Figurine/Figurine', 'rewardCollect', 'Figurine', XpRewards::COLLECT_FIGURINE, AchievementRewards::FIGURINES[1]],
    ['nendoroid', 'collect', 'collect_rewarded', 'Nendoroid/Nendoroid', 'rewardCollect', 'Nendoroid', XpRewards::COLLECT_NENDOROID, AchievementRewards::NENDOROIDS[1]],
    ['peluche', 'collect', 'collect_rewarded', 'Peluche/Peluche', 'rewardCollect', 'Peluche', XpRewards::COLLECT_PELUCHE, AchievementRewards::PELUCHES[1]],
    ['artbook', 'lu', 'xp_read_rewarded', 'Artbook/Artbook', 'rewardArtbookRead', 'Artbook', XpRewards::READ_ARTBOOK, AchievementRewards::ARTBOOKS[1]],
    ['chinois_grammaire', 'maitrise', 'xp_rewarded', 'Chinois/Chinois', 'rewardGrammar', null, XpRewards::LEARN_GRAMMAR, AchievementRewards::GRAMMAR[1]],
    ['chinois_vocabulaire', 'maitrise', 'xp_rewarded', 'Chinois/Chinois', 'rewardVocabulary', null, XpRewards::LEARN_VOCABULARY, AchievementRewards::VOCABULARY[1]]
];
$assert = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};
foreach ($cases as [$table, $status, $flag, $serviceName, $method, $modelName, $baseXp, $achievementXp])
{
    $db->exec('DELETE FROM users');
    $db->exec('DELETE FROM achievement_xp_rewards');
    $db->exec(owned_fixture_sql($db, 'INSERT INTO users VALUES (1, 1, 0)'));
    $db->exec(owned_fixture_sql($db, "CREATE TEMPORARY TABLE $table (id INT PRIMARY KEY, $status INT, $flag INT) ENGINE=InnoDB"));
    $db->exec(owned_fixture_sql($db, "INSERT INTO $table VALUES (1, 1, 0)"));
    $user = new User();
    $user->id = 1;
    $GLOBALS['collectionTestUser'] = $user;
    $service = $container->get('App\\Services\\' . str_replace('/', '\\', $serviceName) . 'XpRewardService');
    $call = static function (int $id) use ($service, $method, $modelName): bool
    {
        if ($modelName === null) return $service->$method($id);
        $class = 'App\\Models\\' . $modelName . '\\' . $modelName;
        $model = new $class();
        $model->id = $id;
        return $service->$method($model);
    };
    $earned = static fn (): int => (int) (($user->level - 1) * $user->level / 2 * 5 + $user->xp);
    try
    {
        $db->transaction(function () use ($call): void
        {
            $call(1);
            throw new RuntimeException('rollback fixture');
        });
    }
    catch (RuntimeException $error)
    {
        if ($error->getMessage() !== 'rollback fixture') throw $error;
    }
    $assert($earned() === 0, "$table: rollback changed in-memory XP");
    $assert((int) $db->query('SELECT xp + level - 1 FROM users WHERE id = 1')->fetchColumn() === 0, "$table: rollback persisted user XP");
    $assert((int) $db->query("SELECT $flag FROM $table WHERE id = 1")->fetchColumn() === 0, "$table: rollback persisted claim");
    $assert((int) $db->query('SELECT COUNT(*) FROM achievement_xp_rewards')->fetchColumn() === 0, "$table: rollback persisted achievements");

    CollectionXpQueryCounter::$queries = [];
    $assert($db->transaction(fn () => $call(1)), "$table: first claim failed");
    $updates = array_filter(CollectionXpQueryCounter::$queries, static fn ($sql) => preg_match('/UPDATE\s+users\b/i', $sql) === 1);
    $locks = array_filter(CollectionXpQueryCounter::$queries, static fn ($sql) => preg_match('/FROM\s+users\b[\s\S]*FOR UPDATE/i', $sql) === 1);
    $assert(count($updates) === 1 && count($locks) === 1, "$table: expected one user lock and update");
    $assert($earned() === $baseXp + $achievementXp, "$table: combined total changed");
    $assert(! $db->transaction(fn () => $call(1)) && $earned() === $baseXp + $achievementXp, "$table: duplicate reward");
    $db->exec(owned_fixture_sql($db, "INSERT INTO $table VALUES (2, 1, 0)"));
    $assert($db->transaction(fn () => $call(2)) && $earned() === 2 * $baseXp + $achievementXp, "$table: base XP lost after previously claimed achievement");

    // Existing base claims must not prevent catching up a missing achievement.
    $db->exec('DELETE FROM achievement_xp_rewards');
    $db->exec('UPDATE users SET level = 1, xp = 0');
    $user->level = 1;
    $user->xp = 0;
    $assert(! $db->transaction(fn () => $call(1)) && $earned() === $achievementXp, "$table: missing achievement was skipped");
    $GLOBALS['collectionTestUser'] = null;
    CollectionXpQueryCounter::$queries = [];
    $assert(! $call(1) && CollectionXpQueryCounter::$queries === [], "$table: unauthenticated reward queried data");
    echo "PASS: $table combined XP, one user lock/update, rollback, duplicates, catch-up and unauthenticated calls.\n";
}
