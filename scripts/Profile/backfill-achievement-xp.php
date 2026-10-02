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

define('ROOT', dirname(__DIR__, 2));
require ROOT . '/vendor/autoload.php';
require ROOT . '/Framework/Support/Helpers.php';

$userId = filter_var($argv[1] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($userId === false || count($argv) > 3 || (isset($argv[2]) && $argv[2] !== '--apply'))
{
    fwrite(STDERR, "Usage: php scripts/Profile/backfill-achievement-xp.php USER_ID [--apply]\n");
    exit(1);
}

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$database = $container->get(Database::class);
$users = $container->get(UserRepository::class);
$dashboardStatsService = $container->get(ProfileStatsService::class);
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
$stats = $dashboardStatsService->getStats($user);
$audit = $rewards->audit($user, $stats);
echo "Diagnostic avant modification (statistiques actuelles) :" . PHP_EOL;
echo 'XP de succès enregistrée : ' . $before . PHP_EOL;
echo 'Total XP calculé : ' . $audit['expectedTotal'] . PHP_EOL;
foreach ($audit['issues'] as $issue)
{
    echo 'Écart : ' . $issue . PHP_EOL;
}
foreach ($audit['missing'] as $key => $xp)
{
    echo "Récompense manquante : $key (+$xp XP)." . PHP_EOL;
}
if ($audit['issues'] === [] && $audit['missing'] === [])
{
    echo 'Aucun écart détecté.' . PHP_EOL;
}
echo '--apply ajoute uniquement les récompenses manquantes ; aucun retrait d’XP ni correction des récompenses existantes.' . PHP_EOL;
if ($apply)
{
    $rewards->rewardAll($user, $stats);
    echo 'XP de succès ajoutée : ' . ($rewards->totalForUser($user) - $before) . PHP_EOL;
}
else
{
    echo 'Lecture seule. XP de succès déjà attribuée : ' . $before . PHP_EOL;
    echo 'Utiliser --apply pour attribuer les récompenses manquantes selon les statistiques actuelles.' . PHP_EOL;
}
