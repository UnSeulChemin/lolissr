<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Profile\ProfileAchievements;
use App\Services\Profile\ProfileStatsService;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__) . '/phpstan-bootstrap.php';

// Isolated fixtures: never connect to the application's MySQL database.
$database = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($database, 'sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$database->exec('CREATE TABLE manga (slug TEXT, numero INT, statut TEXT, lu INT, xp_read_rewarded INT, xp_series_rewarded INT)');
$database->exec('CREATE TABLE artbook (lu INT, xp_read_rewarded INT)');
foreach (['figurine', 'nendoroid', 'peluche'] as $table)
{
    $database->exec("CREATE TABLE $table (collect INT, collect_rewarded INT)");
}
foreach (['chinois_vocabulaire', 'chinois_grammaire'] as $table)
{
    $database->exec("CREATE TABLE $table (maitrise INT, xp_rewarded INT)");
}
$database->exec('CREATE TABLE achievement_xp_rewards (user_id INT, achievement_key TEXT, xp INT)');
$database->exec("INSERT INTO achievement_xp_rewards VALUES (1, 'tomes_1', 50), (2, 'tomes_10', 500)");
for ($i = 1; $i <= 200; $i++)
{
    $database->exec("INSERT INTO manga VALUES ('series_$i', 1, 'termine', 1, 1, 1)");
    foreach (['artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_vocabulaire', 'chinois_grammaire'] as $table)
    {
        $database->exec("INSERT INTO $table VALUES (1, 1)");
    }
}
$database->exec('PRAGMA query_only = ON');

$container = new Container();
$container->instance(Database::class, $database);
$service = $container->get(ProfileStatsService::class);
$user = new User();
$user->id = 1;
$user->level = 200;
$user->xp = 7;

for ($visit = 0; $visit < 2; $visit++)
{
    $stats = $service->getStats($user);
    if ($stats->achievementXp !== 50 || $stats->totalXp !== 29050 || $stats->completedSeries !== 200)
    {
        throw new RuntimeException('Incorrect profile totals or reward leakage between users.');
    }
    if ($user->level !== 200 || $user->xp !== 7 || $database->inTransaction())
    {
        throw new RuntimeException('Reading the profile modified XP or started a transaction.');
    }
}
$achievements = ProfileAchievements::forStats($stats, $user->level);
if (count($achievements) !== 41 || count(array_filter($achievements, static fn ($item) => $item['unlocked'])) !== 41)
{
    throw new RuntimeException('Missing or incorrectly locked achievement thresholds.');
}
if ((int) $database->query('SELECT COUNT(*) FROM achievement_xp_rewards')->fetchColumn() !== 2)
{
    throw new RuntimeException('Reading the profile attributed missing achievements.');
}
echo "PASS: repeated profile reads with all thresholds reached, read-only database, isolated user XP and 41 achievements.\n";

$unlocks = $container->get(\App\Repositories\Profile\ProfileUnlockStatsRepository::class);
$expectedCounters = [
    'forTitles' => ['readTomes', 'completedSeries', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected', 'vocabularyLearned', 'grammarLearned'],
    'forBanners' => ['readTomes', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned'],
    'forFrames' => ['readTomes', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned'],
];
foreach ($expectedCounters as $method => $properties)
{
    $counts = $unlocks->$method();
    foreach ($properties as $property)
    {
        if ($counts->$property !== $stats->$property)
        {
            throw new RuntimeException('Unlock counter differs from profile: ' . $property);
        }
    }
}
$database->exec('PRAGMA query_only = OFF');
$database->exec('DROP TABLE achievement_xp_rewards');
$database->exec('UPDATE manga SET lu = 0');
$database->exec('PRAGMA query_only = ON');
foreach (['forTitles', 'forBanners', 'forFrames'] as $method)
{
    $counts = $unlocks->$method();
    if ($counts->readTomes !== 0 || $counts->vocabularyLearned !== 200)
    {
        throw new RuntimeException('Stale or incorrect unlock counters.');
    }
}
echo "PASS: lightweight unlock counters stay fresh without the XP rewards table.\n";
