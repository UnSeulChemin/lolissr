<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use App\Cache\CacheKey;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Repositories\Profile\ProfileStatsRepository;
use App\Repositories\Profile\ProfileUnlockStatsRepository;
use App\Services\Auth\AuthService;
use App\Services\Manga\MangaXpRewardService;
use App\Services\Profile\ProfileAchievements;
use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$check = static function (bool $ok, string $message): void
{
    if (! $ok) throw new RuntimeException($message);
};

// Cloner le schema reel, sans ses donnees, dans une base de test aleatoire.
// MySQL interdit de referencer deux fois une table temporaire dans une requete.
// Une base jetable permet de tester aussi les jointures, agregats et FK reels.
$tables = ['users', 'manga', 'artbook', 'figurine', 'nendoroid', 'peluche',
    'chinois_grammaire', 'chinois_vocabulaire', 'manga_series_rewards', 'achievement_xp_rewards', 'login_attempts'];
$definitions = [];
foreach ($tables as $table)
{
    $sql = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
    $definitions[] = $sql;
}
$originalDatabase = (string) $db->query('SELECT DATABASE()')->fetchColumn();
$fixtureDatabase = 'lolissr_test_ownership_' . bin2hex(random_bytes(8));
$db->exec("CREATE DATABASE `$fixtureDatabase` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
try
{
    $db->exec("USE `$fixtureDatabase`");
    foreach ($definitions as $sql) $db->exec($sql);
    $db->exec("INSERT INTO users (id, username, password, is_admin) VALUES (1, 'owner-fixture', 'fixture', 1), (2, 'other-fixture', 'fixture', 0)");
    $users = $container->get(UserRepository::class);
    $owner = $users->findById(1);
    $other = $users->findById(2);
    $GLOBALS['testCurrentUser'] = $owner;

    $cases = [
        'Manga' => ['slug' => 'private-fixture', 'numero' => 1, 'livre' => 'Private Fixture', 'statut' => 'termine'],
        'Artbook' => ['slug' => 'private-fixture', 'numero' => 1, 'artbook' => 'Private Fixture', 'auteur' => 'Fixture', 'company' => 'Fixture'],
        'Figurine' => ['slug' => 'private-fixture', 'numero' => 1, 'waifu' => 'Private Fixture', 'origin' => 'Fixture', 'company' => 'Fixture', 'scale' => '1/7'],
        'Nendoroid' => ['slug' => 'private-fixture', 'numero' => 1, 'waifu' => 'Private Fixture', 'origin' => 'Fixture', 'company' => 'Fixture'],
        'Peluche' => ['slug' => 'private-fixture', 'numero' => 1, 'waifu' => 'Private Fixture', 'origin' => 'Fixture', 'company' => 'Fixture']
    ];
    $repositories = [];
    $ownerIds = [];
    $otherIds = [];
    foreach ($cases as $kind => $data)
    {
        $table = strtolower($kind);
        $repo = $container->get("App\\Repositories\\$kind\\{$kind}Repository");
        $repositories[$kind] = $repo;
        $check($repo->insert($data + ['thumbnail' => 'fixture', 'extension' => 'webp', 'user_id' => 2]), 'Cannot create owner fixture');
        $ownerIds[$kind] = (int) $db->lastInsertId();
        $check((int) $db->query("SELECT user_id FROM $table WHERE id = " . $ownerIds[$kind])->fetchColumn() === 1,
            'Caller supplied ownership was accepted');
        $status = in_array($kind, ['Manga', 'Artbook'], true) ? 'lu' : 'collect';
        $db->exec("UPDATE $table SET $status = 1 WHERE user_id = 1");
    }
    $grammar = $container->get(ChinoisGrammaireRepository::class);
    $vocabulary = $container->get(ChinoisVocabulaireRepository::class);
    $grammarData = ['niveau' => 'HSK1', 'titre' => 'Private Fixture', 'structure' => 'Fixture', 'phrase' => 'Fixture',
        'pinyin' => 'Fixture', 'traduction' => 'Fixture', 'explication' => 'Fixture', 'section' => 'Fixture', 'categorie' => 'Fixture'];
    $vocabularyData = ['langue' => 'mandarin', 'mot' => 'Private Fixture', 'pinyin' => 'Fixture', 'type' => 'Fixture',
        'traduction' => 'Fixture', 'exemple' => 'Fixture'];
    $grammar->insert($grammarData);
    $ownerGrammarId = (int) $db->lastInsertId();
    $vocabulary->insert($vocabularyData);
    $ownerVocabularyId = (int) $db->lastInsertId();
    $db->exec('UPDATE chinois_grammaire SET maitrise = 1');
    $db->exec('UPDATE chinois_vocabulaire SET maitrise = 1');

    $stats = $container->get(ProfileStatsRepository::class);
    $unlocks = $container->get(ProfileUnlockStatsRepository::class);
    $ownerCache = CacheKey::dashboard();
    $GLOBALS['testCurrentUser'] = $other;
    $check($ownerCache !== CacheKey::dashboard(), 'Dashboard cache key is shared');
    $check(array_filter($stats->summary(2)) === [], 'A second account inherited collection progress/XP');
    $achievements = ProfileAchievements::forStats($unlocks->forAchievements(), $other->level);
    $check($achievements === ProfileAchievements::forStats(new \App\DTO\Profile\Responses\ProfileUnlockStatsData(), $other->level), 'New account inherited achievements');

    foreach ($cases as $kind => $data)
    {
        $repo = $repositories[$kind];
        $read = $kind === 'Manga' ? 'findRecordBySlugAndNumero' : 'findOneBySlugAndNumero';
        $check($repo->$read('private-fixture', 1) === null, "$kind foreign record visible");
        $check($repo->deleteById($ownerIds[$kind]) === false, "$kind foreign record deleted");
        $repo->update(['commentaire' => 'foreign-write', 'user_id' => 2], ['id' => $ownerIds[$kind]]);
        $table = strtolower($kind);
        $check($db->query("SELECT commentaire FROM $table WHERE id = " . $ownerIds[$kind])->fetchColumn() === null,
            "$kind foreign record updated");
        $check($repo->insert($data + ['thumbnail' => 'fixture-other', 'extension' => 'webp']), "$kind identical slug rejected across owners");
        $otherIds[$kind] = (int) $db->lastInsertId();
        $record = $repo->$read('private-fixture', 1);
        $check($record !== null && $record->user_id === 2 && $record->id === $otherIds[$kind], "$kind record resolved to wrong owner");
        $claim = in_array($kind, ['Manga', 'Artbook'], true) ? 'claimReadReward' : 'claimCollectReward';
        $check($repo->$claim($ownerIds[$kind]) === false, "$kind foreign XP claimed");
        $search = $container->get("App\\Repositories\\$kind\\{$kind}SearchRepository")->search('Private Fixture');
        $check(count($search) === 1, "$kind search leaked another owner's record");
    }
    $check($grammar->findById($ownerGrammarId) === null && $vocabulary->findById($ownerVocabularyId) === null,
        'Foreign learning content visible by ID');
    $check($grammar->toggleMaitrise($ownerGrammarId) === null && $vocabulary->toggleMaitrise($ownerVocabularyId) === null,
        'Foreign learning progress modified');
    $check(! $grammar->claimXpReward($ownerGrammarId) && ! $vocabulary->claimXpReward($ownerVocabularyId), 'Foreign learning XP claimed');
    $check(! $grammar->deleteGrammaire($ownerGrammarId) && ! $vocabulary->deleteVocabulaire($ownerVocabularyId), 'Foreign learning content deleted');
    $grammar->insert($grammarData);
    $otherGrammarId = (int) $db->lastInsertId();
    $vocabulary->insert($vocabularyData);
    $otherVocabularyId = (int) $db->lastInsertId();
    $check($grammar->findNotMasteredDto()[0]->id === $otherGrammarId && $vocabulary->findNotMasteredDto()[0]->id === $otherVocabularyId,
        'Learning deck includes foreign records');
    $learningSearch = $container->get(\App\Repositories\Chinois\ChinoisSearchRepository::class)->search('Private Fixture');
    $check(count($learningSearch) === 2, 'Learning search leaked foreign records');
    $check(array_filter($stats->summary(2)) === [], 'Own unstarted collection must have zero progress');

    // Meme serie terminee : chacun recoit sa recompense, une seule fois, sans
    // consommer les indicateurs de l'autre. Reutiliser les memes repositories teste
    // aussi les changements de compte dans un processus persistant.
    $mangaRepo = $repositories['Manga'];
    $rewardService = $container->get(MangaXpRewardService::class);
    $db->transaction(function () use ($mangaRepo, $rewardService, $check): void
    {
        $mangaRepo->updateReadStatus('private-fixture', 1, true);
        $manga = $mangaRepo->findRecordBySlugAndNumero('private-fixture', 1);
        $check($rewardService->rewardRead($manga, 'private-fixture') === ['xpEarned' => true, 'seriesXpEarned' => true],
            'Second account did not receive independent rewards');
    });
    $check((int) $db->query('SELECT xp_read_rewarded FROM manga WHERE user_id = 1')->fetchColumn() === 0,
        'Second account consumed first account reward');
    $otherXp = [$other->level, $other->xp];
    $db->transaction(function () use ($mangaRepo, $rewardService, $check): void
    {
        $manga = $mangaRepo->findRecordBySlugAndNumero('private-fixture', 1);
        $check($rewardService->rewardRead($manga, 'private-fixture') === ['xpEarned' => false, 'seriesXpEarned' => false], 'Duplicate personal reward');
    });
    $check([$other->level, $other->xp] === $otherXp, 'Duplicate personal XP changed');
    $GLOBALS['testCurrentUser'] = $owner;
    $db->transaction(function () use ($mangaRepo, $rewardService, $check): void
    {
        $manga = $mangaRepo->findRecordBySlugAndNumero('private-fixture', 1);
        $check($rewardService->rewardRead($manga, 'private-fixture') === ['xpEarned' => true, 'seriesXpEarned' => true],
            'First account reward was blocked by second account');
    });
    $check((int) $db->query('SELECT COUNT(*) FROM manga_series_rewards')->fetchColumn() === 2, 'Series journal is not keyed by user');
    $check([$owner->level, $owner->xp] === $otherXp, 'Identical personal progress should grant identical XP');
    $check(count($db->query('SELECT DISTINCT user_id FROM achievement_xp_rewards')->fetchAll()) === 2, 'Achievement history missing user isolation');

    $registration = $container->get(AuthService::class);
    $check($registration->register('fresh-ownership-fixture', 'fixture-password-123'), 'Cannot register a new account');
    $new = $users->findByUsername('fresh-ownership-fixture');
    $check($new !== null && $new->level === 1 && $new->xp === 0 && ! $new->is_admin, 'New account defaults or privileges invalid');
    $check(array_filter($stats->summary($new->id)) === [], 'Registered account inherited progress');

    $GLOBALS['testCurrentUser'] = null;
    $check($mangaRepo->findRecordBySlugAndNumero('private-fixture', 1) === null, 'Anonymous read exposed private record');
    $check($grammar->toggleMaitrise($ownerGrammarId) === null, 'Anonymous mutation modified private record');
    try
    {
        $mangaRepo->insert($cases['Manga'] + ['thumbnail' => 'fixture', 'extension' => 'webp']);
        throw new RuntimeException('Anonymous insert accepted');
    }
    catch (LogicException)
    {
    }
    echo "PASS: seven private domains, independent accounts/XP/achievements, identical slugs, forbidden foreign reads/writes, personal series history, fresh registration and separate cache keys. Disposable production-schema database; historical data unchanged.\n";
}
finally
{
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('USE `' . str_replace('`', '``', $originalDatabase) . '`');
    // Nom cree plus haut a partir d'un prefixe fixe et de donnees aleatoires hex.
    $db->exec("DROP DATABASE `$fixtureDatabase`");
}
