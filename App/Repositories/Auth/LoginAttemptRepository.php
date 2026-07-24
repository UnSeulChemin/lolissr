<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Models\Model;

final class LoginAttemptRepository extends Model
{
    protected string $table = 'login_attempts';

    // =========================================
    // RECHERCHE
    // =========================================

    /**
     * @return array{
     *     attempts: int,
     *     firstAttemptAt: string,
     *     lockedUntil: string|null
     * }|null
     */
    public function findByIdentifierHash(string $identifierHash): ?array
    {
        $result = $this->fetchOne(
            "
            SELECT
                attempts,
                first_attempt_at,
                locked_until
            FROM {$this->table()}
            WHERE identifier_hash = :identifier_hash
            LIMIT 1
            ",
            ['identifier_hash' => $identifierHash]
        );

        if ($result === null)
        {
            return null;
        }

        return [
            'attempts' => (int) $result->attempts,
            'firstAttemptAt' => (string) $result->first_attempt_at,
            'lockedUntil' => $result->locked_until !== null
                ? (string) $result->locked_until
                : null,
        ];
    }

    // =========================================
    // TENTATIVES
    // =========================================

    public function createAttempt(string $identifierHash, string $attemptedAt): bool
    {
        return $this->insert([
            'identifier_hash' => $identifierHash,
            'attempts' => 1,
            'first_attempt_at' => $attemptedAt,
            'locked_until' => null,
        ]);
    }

    public function incrementAttempts(string $identifierHash, int $attempts): bool
    {
        return $this->update(
            ['attempts' => $attempts],
            ['identifier_hash' => $identifierHash]
        );
    }

    public function resetWindow(string $identifierHash, string $attemptedAt): bool
    {
        return $this->update(
            [
                'attempts' => 1,
                'first_attempt_at' => $attemptedAt,
                'locked_until' => null,
            ],
            ['identifier_hash' => $identifierHash]
        );
    }

    public function lock(
        string $identifierHash,
        int $attempts,
        string $lockedUntil
    ): bool {
        return $this->update(
            [
                'attempts' => $attempts,
                'locked_until' => $lockedUntil,
            ],
            ['identifier_hash' => $identifierHash]
        );
    }

    public function clear(string $identifierHash): bool
    {
        return $this->delete([
            'identifier_hash' => $identifierHash,
        ]);
    }

    // =========================================
    // NETTOYAGE
    // =========================================

    public function deleteExpired(string $expiredBefore): bool
    {
        return $this->execute(
            "
            DELETE FROM {$this->table()}
            WHERE first_attempt_at < :expired_before
              AND (
                  locked_until IS NULL
                  OR locked_until < :expired_before
              )
            ",
            ['expired_before' => $expiredBefore]
        );
    }
}