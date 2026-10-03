<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Profile\ProfileAchievements;
use App\Services\Profile\ProfileStatsService;
use Framework\Container\Container;
use Framework\Database\Database;

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

final class ProfileQueryCounter extends PDOStatement
{
    public static int $executions = 0;
    public function execute(?array $params = null): bool
    {
        self::$executions++;
        return parent::execute($params);
    }
}

// Isolated fixtures: never connect to the application's MySQL database.
$database = (new ReflectionClass(Database::class))->newInstanceWithoutConstructor();
(new ReflectionMethod(PDO::class, '__construct'))->invoke($database, 'sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$database->setAttribute(PDO::ATTR_STATEMENT_CLASS, [ProfileQueryCounter::class]);
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
    ProfileQueryCounter::$executions = 0;
    $stats = $service->getStats($user);
    if (ProfileQueryCounter::$executions !== 1) throw new RuntimeException('Profile did not use one query.');
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
if (count($achievements) !== 44 || count(array_filter($achievements, static fn ($item) => $item['unlocked'])) !== 44)
{
    throw new RuntimeException('Missing or incorrectly locked achievement thresholds.');
}
if ((int) $database->query('SELECT COUNT(*) FROM achievement_xp_rewards')->fetchColumn() !== 2)
{
    throw new RuntimeException('Reading the profile attributed missing achievements.');
}
echo "PASS: repeated profile reads with all thresholds reached, read-only database, isolated user XP and 44 achievements.\n";

$catalog = new \App\Services\Profile\ProfileImageCatalog();
foreach ([0, 1, 9, 10, 24, 25] as $level)
{
    $items = ProfileAchievements::forStats(new \App\DTO\Profile\Responses\ProfileUnlockStatsData(), $level);
    $baseCount = count(array_filter($items, static fn ($item) => $item['category'] !== 'Succès' && $item['unlocked']));
    foreach (array_filter($items, static fn ($item) => $item['category'] === 'Succès') as $item)
    {
        if ($item['current'] !== $baseCount || $item['unlocked'] !== ($baseCount >= $item['target']))
            throw new RuntimeException('Achievement rewards counted themselves.');
    }
}
foreach ([0, 1, 9, 10, 24, 25] as $count)
{
    foreach ($catalog->avatarsForAchievements($count) as $avatar)
    {
        $target = array_search($avatar['avatar'], \App\Services\Profile\ProfileImageCatalog::ACHIEVEMENT_REWARD_AVATARS, true);
        if ($avatar['unlocked'] !== ($target === false || $count >= $target))
            throw new RuntimeException('Incorrect exclusive avatar unlock.');
    }
}

$unlocks = $container->get(\App\Repositories\Profile\ProfileUnlockStatsRepository::class);
if (ProfileAchievements::forStats($unlocks->forAchievements(), $user->level) !== $achievements)
{
    throw new RuntimeException('Lightweight achievements differ from full profile stats.');
}
$expectedCounters = [
    'forTitles' => ['readTomes', 'completedSeries', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected', 'vocabularyLearned', 'grammarLearned'],
    'forBanners' => ['readTomes', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned'],
    'forFrames' => ['readTomes', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned'],
];
foreach ($expectedCounters as $method => $properties)
{
    ProfileQueryCounter::$executions = 0;
    $counts = $unlocks->$method();
    if (ProfileQueryCounter::$executions !== 1) throw new RuntimeException('Unlock counters must use one query: ' . $method);
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
foreach (['forTitles', 'forBanners', 'forFrames', 'forAchievements'] as $method)
{
    $counts = $unlocks->$method();
    if ($counts->readTomes !== 0 || $counts->vocabularyLearned !== 200)
    {
        throw new RuntimeException('Stale or incorrect unlock counters.');
    }
}
echo "PASS: lightweight unlock counters stay fresh without the XP rewards table.\n";

foreach ([
    [\App\Repositories\Manga\MangaStatsRepository::class, 'countRead', 0],
    [\App\Repositories\Artbook\ArtbookStatsRepository::class, 'countRead', 200],
    [\App\Repositories\Figurine\FigurineStatsRepository::class, 'countCollected', 200],
    [\App\Repositories\Nendoroid\NendoroidStatsRepository::class, 'countCollected', 200],
    [\App\Repositories\Peluche\PelucheStatsRepository::class, 'countCollected', 200],
    [\App\Repositories\Chinois\ChinoisVocabulaireStatsRepository::class, 'countMastered', 200],
    [\App\Repositories\Chinois\ChinoisGrammaireStatsRepository::class, 'countMastered', 200],
] as [$class, $method, $expected])
{
    if ($container->get($class)->$method() !== $expected) throw new RuntimeException('Incorrect action counter');
}
echo "PASS: targeted action counters.\n";

$database->exec('PRAGMA query_only = OFF');
foreach (['manga', 'artbook', 'figurine', 'nendoroid', 'peluche', 'chinois_vocabulaire', 'chinois_grammaire'] as $table)
{
    $database->exec("DELETE FROM $table");
}
$database->exec('CREATE TABLE achievement_xp_rewards (user_id INT, achievement_key TEXT, xp INT)');
$empty = $service->getStats($user);
if (array_filter(get_object_vars($empty), static fn ($value) => $value !== 0) !== [])
{
    throw new RuntimeException('Empty collections produced nonzero profile stats.');
}
$database->exec("INSERT INTO manga VALUES ('mixed', 1, 'termine', 1, 1, 1), ('mixed', 2, 'termine', 0, 1, 1)");
$database->exec('INSERT INTO artbook VALUES (0, 1)');
$database->exec("INSERT INTO achievement_xp_rewards VALUES (1, 'fixture', 7), (2, 'fixture', 999)");
$database->exec('PRAGMA query_only = ON');
$mixed = $service->getStats($user);
if ($mixed->readTomes !== 1 || $mixed->completedSeries !== 0 || $mixed->readArtbooks !== 0
    || $mixed->tomeXp !== 10 || $mixed->seriesXp !== 20 || $mixed->artbookXp !== 20
    || $mixed->achievementXp !== 7 || $mixed->totalXp !== 57)
{
    throw new RuntimeException('Grouped profile changed mixed read/reward flags or user isolation.');
}
echo "PASS: empty and mixed profile collections preserve reward history and user isolation.\n";
