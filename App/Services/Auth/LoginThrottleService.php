<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\Auth\LoginAttemptRepository;
use App\Repositories\Auth\UserRepository;

use DateTimeImmutable;
use DateTimeZone;

final readonly class LoginThrottleService
{
    private const MAX_ATTEMPTS = 5;

    private const ATTEMPT_WINDOW_MINUTES = 10;
    private const LOCK_DURATION_MINUTES = 15;

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private LoginAttemptRepository $loginAttemptRepository,
        private UserRepository $userRepository
    )
    {}

    // =================================================
    // LIMITATION
    // =================================================

    public function isLocked(string $username, string $ipAddress): bool
    {
        return $this->remainingLockMinutes($username, $ipAddress) > 0;
    }

    public function remainingLockMinutes(string $username, string $ipAddress): int
    {
        $remaining = 0;
        $now = $this->now()->getTimestamp();
        foreach ($this->budgets($username, $ipAddress) as $budget)
        {
            $attempt = $this->loginAttemptRepository->findByIdentifierHash($budget['hash']);
            if ($attempt === null || $attempt['lockedUntil'] === null) continue;
            $remaining = max($remaining, $this->date($attempt['lockedUntil'])->getTimestamp() - $now);
        }
        return (int) ceil($remaining / 60);
    }

    public function recordFailure(string $username, string $ipAddress): bool
    {
        $now = $this->now();

        $locked = false;
        foreach ($this->budgets($username, $ipAddress) as $budget)
        {
            $budgetLocked = $this->loginAttemptRepository->recordFailure(
                $budget['hash'],
                $this->formatDate($now),
                $this->formatDate($now->modify('-' . self::ATTEMPT_WINDOW_MINUTES . ' minutes')),
                $this->formatDate($now->modify('+' . $budget['minutes'] . ' minutes')),
                $budget['attempts']
            );
            $locked = $budgetLocked || $locked;
        }

        // Effectuer au plus un nettoyage limité par heure lorsque le cache est activé.
        try
        {
            \Framework\Cache\Cache::remember('auth.login-attempts.cleanup', 3600, function () use ($now): bool
            {
                $this->loginAttemptRepository->purgeExpired(
                    $this->formatDate($now->modify('-1 day')),
                    $this->formatDate($now)
                );
                return true;
            });
        }
        catch (\Throwable $error)
        {
            \Framework\Logging\Logger::exception($error, ['action' => 'login_attempts_cleanup']);
        }

        return $locked;
    }

    public function clear(string $username, string $ipAddress): void
    {
        $this->loginAttemptRepository->clear($this->identifierHash($username, $ipAddress));
        // A successful login clears this account, but cannot reset an IP's shared budget.
        $budgets = $this->budgets($username, $ipAddress);
        $this->loginAttemptRepository->clear($budgets[1]['hash']);
    }

    // =================================================
    // IDENTIFIANT
    // =================================================

    /** @return list<array{hash: string, attempts: int, minutes: int}> */
    private function budgets(string $username, string $ipAddress): array
    {
        $username = $this->normalizedUsername($username);
        $ip = $this->normalizeIpAddress($ipAddress);
        return [
            ['hash' => hash('sha256', $ip . "\0" . $username), 'attempts' => self::MAX_ATTEMPTS, 'minutes' => self::LOCK_DURATION_MINUTES],
            // A short account cooldown limits distributed attempts without a long global lockout.
            ['hash' => hash('sha256', "account\0" . $username), 'attempts' => 20, 'minutes' => 2],
            ['hash' => hash('sha256', "ip\0" . $ip), 'attempts' => 50, 'minutes' => self::LOCK_DURATION_MINUTES]
        ];
    }

    private function normalizedUsername(string $username): string
    {
        // Résoudre avec la même collation que l’authentification. Conserver
        // l’orthographe enregistrée pour préserver les compteurs existants du compte.
        $username = $this->userRepository->findByUsername($username)->username ?? $username;
        return mb_strtolower(trim($username));
    }

    private function identifierHash(string $username, string $ipAddress): string
    {
        return hash('sha256', $this->normalizeIpAddress($ipAddress) . "\0" . $this->normalizedUsername($username));
    }

    private function normalizeIpAddress(string $ipAddress): string
    {
        $packedIpAddress = @inet_pton(trim($ipAddress));

        if ($packedIpAddress === false)
        {
            return 'unknown';
        }

        $normalizedIpAddress = inet_ntop($packedIpAddress);

        return $normalizedIpAddress !== false
            ? $normalizedIpAddress
            : 'unknown';
    }

    // =================================================
    // DATE
    // =================================================

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    private function date(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date, new DateTimeZone('UTC'));
    }

    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->format(self::DATE_FORMAT);
    }
}
