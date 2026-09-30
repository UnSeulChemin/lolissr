<?php

declare(strict_types=1);

use App\Repositories\Auth\UserRepository;
use App\Services\Profile\AchievementXpService;
use App\Services\Profile\ProfileStatsService;
use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

$userId = filter_var($argv[1] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($userId === false || count($argv) > 3 || (isset($argv[2]) && $argv[2] !== '--apply'))
{
    fwrite(STDERR, "Usage: php scripts/backfill-achievement-xp.php USER_ID [--apply]\n");
    exit(1);
}

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$database = $container->get(Database::class);
$users = $container->get(UserRepository::class);
$statsService = $container->get(ProfileStatsService::class);
$rewards = $container->get(AchievementXpService::class);
$user = $users->findById($userId);
if ($user === null)
{
    fwrite(STDERR, "Utilisateur introuvable.\n");
    exit(1);
}

$apply = ($argv[2] ?? '') === '--apply';
if (! $apply)
{
    $database->exec('SET SESSION TRANSACTION READ ONLY');
}
$before = $rewards->totalForUser($user);
$stats = $statsService->getStats($user);
if ($apply)
{
    $database->transaction(static function () use ($rewards, $user, $stats): void {
        $rewards->rewardTomes($user, $stats->readTomes);
        $rewards->rewardSeries($user, $stats->completedSeries);
        $rewards->rewardArtbooks($user, $stats->readArtbooks);
        $rewards->rewardFigurines($user, $stats->figurinesCollected);
        $rewards->rewardNendoroids($user, $stats->nendoroidsCollected);
        $rewards->rewardPeluches($user, $stats->peluchesCollected);
        $rewards->rewardVocabulary($user, $stats->vocabularyLearned);
        $rewards->rewardGrammar($user, $stats->grammarLearned);
    });
    echo 'XP de succès ajoutée : ' . ($rewards->totalForUser($user) - $before) . PHP_EOL;
}
else
{
    echo 'Lecture seule. XP de succès déjà attribuée : ' . $before . PHP_EOL;
    echo 'Utiliser --apply pour attribuer les récompenses manquantes selon les statistiques actuelles.' . PHP_EOL;
}
