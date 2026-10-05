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
$check = static function (bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
};
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, username VARCHAR(50) COLLATE utf8mb4_0900_ai_ci, password VARCHAR(255))'));
$db->exec(owned_fixture_sql($db, 'CREATE TEMPORARY TABLE login_attempts (identifier_hash CHAR(64) PRIMARY KEY, attempts INT, first_attempt_at DATETIME, locked_until DATETIME NULL)'));
try
{
    $insert = $db->prepare(owned_fixture_sql($db, 'INSERT INTO users VALUES (?, ?, ?)'));
    foreach ([[1, 'test'], [2, 'other']] as [$id, $name]) $insert->execute([$id, $name, password_hash('correct-password', PASSWORD_DEFAULT)]);
    $auth = $container->get(AuthService::class);
    $throttle = $container->get(LoginThrottleService::class);
    $ip = '192.0.2.1';
    // Preserve the pre-existing counter keyed by the stored username spelling.
    $db->prepare(owned_fixture_sql($db, 'INSERT INTO login_attempts VALUES (?, 1, ?, NULL)'))->execute([
        hash('sha256', $ip . "\0test"), gmdate('Y-m-d H:i:s')
    ]);
    foreach (['tést', 'tèst', "te\u{0301}st"] as $name)
    {
        $check($auth->login($name, 'wrong-password', $ip) === LoginResult::INVALID_CREDENTIALS, 'Premature lock');
    }
    $check($auth->login('TEST', 'wrong-password', $ip) === LoginResult::LOCKED, 'Variants bypass the shared limit');
    $check((int) $db->query('SELECT COUNT(*) FROM login_attempts')->fetchColumn() === 3, 'Variants created separate counters within budgets');
    $check($throttle->remainingLockMinutes('tést', $ip) > 0, 'Lock duration lookup uses a different identity');
    $check($auth->login('tèst', 'correct-password', $ip) === LoginResult::LOCKED, 'Locked variant accepted');
    $check(!$throttle->isLocked('other', $ip), 'Other account shares the lock');
    $check(!$throttle->isLocked('test', '192.0.2.2'), 'Other IP shares the lock');
    $throttle->clear('tést', $ip);
    $check(!$throttle->isLocked('TEST', $ip), 'Clear through alias did not clear the canonical counter');
    $check($auth->login('missing', 'wrong-password', $ip) === LoginResult::INVALID_CREDENTIALS, 'Unknown username fails unexpectedly');
    $db->exec('DELETE FROM login_attempts');
    for ($i = 1; $i <= 20; $i++) $throttle->recordFailure('TEST', '192.0.2.' . $i);
    $check($throttle->isLocked('tést', '198.51.100.1'), 'Changing IP bypasses account budget');
    $check($throttle->remainingLockMinutes('test', '198.51.100.1') <= 2, 'Account cooldown is too long');
    $check(!$throttle->isLocked('other', '198.51.100.1'), 'Account budget blocks unrelated users');
    $db->exec("UPDATE login_attempts SET locked_until = '2000-01-01', first_attempt_at = '2000-01-01'");
    $check(!$throttle->isLocked('test', '198.51.100.1'), 'Expired account budget remains locked');
    $db->exec('DELETE FROM login_attempts');
    for ($i = 1; $i <= 50; $i++) $throttle->recordFailure('missing-' . $i, '203.0.113.1');
    $check($throttle->isLocked('other', '203.0.113.1'), 'Changing account bypasses IP budget');
    $throttle->clear('other', '203.0.113.1');
    $check($throttle->isLocked('other', '203.0.113.1'), 'Account success can reset shared IP budget');
    $check(!$throttle->isLocked('other', '203.0.113.2'), 'IP budget blocks unrelated IPs');
    $credentials = new ReflectionMethod($auth, 'hasValidCredentials');
    $check(!$credentials->invoke($auth, 'new-user', '12345678901') && $credentials->invoke($auth, 'new-user', '123456789012'), 'New password minimum incorrect');
    $check(!$credentials->invoke($auth, 'new-user', str_repeat('é', 37)), 'Bcrypt byte limit bypassed');
    $db->exec('DELETE FROM login_attempts');
    $db->prepare('UPDATE users SET password = ? WHERE id = 1')->execute([password_hash('old123', PASSWORD_DEFAULT)]);
    $check($auth->login('test', 'old123', '192.0.2.1') === LoginResult::SUCCESS, 'Existing short password no longer works');
    $auth->logout();
}
finally
{
    $db->exec('DROP TEMPORARY TABLE login_attempts');
    $db->exec('DROP TEMPORARY TABLE users');
}
echo "PASS: database-equivalent login names share existing counters, duration, lock and clear; account/IP isolation preserved.\n";
