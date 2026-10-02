<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use App\Enums\Auth\LoginResult;
use App\Services\Auth\AuthService;
use App\Services\Auth\LoginThrottleService;
use Framework\Application\Bootstrap;
use Framework\Container\Container;
use Framework\Database\Database;

Bootstrap::loadEnvOnly();
$container = new Container();
$container->singleton(Database::class);
$db = $container->get(Database::class);
$check = static function (bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
};
$db->exec('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, username VARCHAR(50) COLLATE utf8mb4_0900_ai_ci, password VARCHAR(255))');
$db->exec('CREATE TEMPORARY TABLE login_attempts (identifier_hash CHAR(64) PRIMARY KEY, attempts INT, first_attempt_at DATETIME, locked_until DATETIME NULL)');
try
{
    $insert = $db->prepare('INSERT INTO users VALUES (?, ?, ?)');
    foreach ([[1, 'test'], [2, 'other']] as [$id, $name]) $insert->execute([$id, $name, password_hash('correct-password', PASSWORD_DEFAULT)]);
    $auth = $container->get(AuthService::class);
    $throttle = $container->get(LoginThrottleService::class);
    $ip = '192.0.2.1';
    // Preserve the pre-existing counter keyed by the stored username spelling.
    $db->prepare('INSERT INTO login_attempts VALUES (?, 1, ?, NULL)')->execute([
        hash('sha256', $ip . "\0test"), gmdate('Y-m-d H:i:s'),
    ]);
    foreach (['tést', 'tèst', "te\u{0301}st"] as $name)
    {
        $check($auth->login($name, 'wrong-password', $ip) === LoginResult::INVALID_CREDENTIALS, 'Premature lock');
    }
    $check($auth->login('TEST', 'wrong-password', $ip) === LoginResult::LOCKED, 'Variants bypass the shared limit');
    $check((int) $db->query('SELECT COUNT(*) FROM login_attempts')->fetchColumn() === 1, 'Variants created separate counters');
    $check($throttle->remainingLockMinutes('tést', $ip) > 0, 'Lock duration lookup uses a different identity');
    $check($auth->login('tèst', 'correct-password', $ip) === LoginResult::LOCKED, 'Locked variant accepted');
    $check(!$throttle->isLocked('other', $ip), 'Other account shares the lock');
    $check(!$throttle->isLocked('test', '192.0.2.2'), 'Other IP shares the lock');
    $throttle->clear('tést', $ip);
    $check(!$throttle->isLocked('TEST', $ip), 'Clear through alias did not clear the canonical counter');
    $check($auth->login('missing', 'wrong-password', $ip) === LoginResult::INVALID_CREDENTIALS, 'Unknown username fails unexpectedly');
}
finally
{
    $db->exec('DROP TEMPORARY TABLE login_attempts');
    $db->exec('DROP TEMPORARY TABLE users');
}
echo "PASS: database-equivalent login names share existing counters, duration, lock and clear; account/IP isolation preserved.\n";
