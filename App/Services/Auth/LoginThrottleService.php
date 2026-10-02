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
    ) {}

    // =========================================
    // LIMITATION
    // =========================================

    public function isLocked(string $username, string $ipAddress): bool
    {
        return $this->remainingLockMinutes($username, $ipAddress) > 0;
    }

    public function remainingLockMinutes(string $username, string $ipAddress): int
    {
        $attempt = $this->loginAttemptRepository->findByIdentifierHash(
            $this->identifierHash($username, $ipAddress)
        );

        if ($attempt === null || $attempt['lockedUntil'] === null)
        {
            return 0;
        }

        $remainingSeconds = $this->date($attempt['lockedUntil'])->getTimestamp()
            - $this->now()->getTimestamp();

        if ($remainingSeconds <= 0)
        {
            return 0;
        }

        return (int) ceil($remainingSeconds / 60);
    }

    public function recordFailure(string $username, string $ipAddress): bool
    {
        $now = $this->now();

        $locked = $this->loginAttemptRepository->recordFailure(
            $this->identifierHash($username, $ipAddress),
            $this->formatDate($now),
            $this->formatDate($now->modify('-' . self::ATTEMPT_WINDOW_MINUTES . ' minutes')),
            $this->formatDate($now->modify('+' . self::LOCK_DURATION_MINUTES . ' minutes')),
            self::MAX_ATTEMPTS
        );

        // At most one bounded cleanup per hour when the cache is enabled.
        try
        {
            \Framework\Cache\Cache::remember('auth.login-attempts.cleanup', 3600, function () use ($now): bool {
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
        $this->loginAttemptRepository->clear(
            $this->identifierHash($username, $ipAddress)
        );
    }

    // =========================================
    // IDENTIFIANT
    // =========================================

    private function identifierHash(string $username, string $ipAddress): string
    {
        // Resolve using the same database collation as authentication. Keep the
        // stored spelling so existing counters for that account remain valid.
        $username = $this->userRepository->findByUsername($username)->username ?? $username;
        $normalizedUsername = mb_strtolower(trim($username));
        $normalizedIpAddress = $this->normalizeIpAddress($ipAddress);

        return hash(
            'sha256',
            $normalizedIpAddress . "\0" . $normalizedUsername
        );
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

    // =========================================
    // DATE
    // =========================================

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
