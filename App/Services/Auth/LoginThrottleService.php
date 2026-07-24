<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\Auth\LoginAttemptRepository;

use DateTimeImmutable;
use DateTimeZone;

final readonly class LoginThrottleService
{
    private const MAX_ATTEMPTS = 5;

    private const ATTEMPT_WINDOW_MINUTES = 10;
    private const LOCK_DURATION_MINUTES = 15;

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private LoginAttemptRepository $loginAttemptRepository
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

    public function recordFailure(string $username, string $ipAddress): void
    {
        if ($this->isLocked($username, $ipAddress))
        {
            return;
        }

        $now = $this->now();
        $identifierHash = $this->identifierHash($username, $ipAddress);
        $attempt = $this->loginAttemptRepository->findByIdentifierHash($identifierHash);

        if ($attempt === null)
        {
            $this->loginAttemptRepository->createAttempt(
                $identifierHash,
                $this->formatDate($now)
            );

            return;
        }

        $firstAttemptAt = $this->date($attempt['firstAttemptAt']);
        $windowStart = $now->modify('-' . self::ATTEMPT_WINDOW_MINUTES . ' minutes');

        if ($firstAttemptAt < $windowStart)
        {
            $this->loginAttemptRepository->resetWindow(
                $identifierHash,
                $this->formatDate($now)
            );

            return;
        }

        $attempts = $attempt['attempts'] + 1;

        if ($attempts < self::MAX_ATTEMPTS)
        {
            $this->loginAttemptRepository->incrementAttempts(
                $identifierHash,
                $attempts
            );

            return;
        }

        $lockedUntil = $now->modify('+' . self::LOCK_DURATION_MINUTES . ' minutes');

        $this->loginAttemptRepository->lock(
            $identifierHash,
            $attempts,
            $this->formatDate($lockedUntil)
        );
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