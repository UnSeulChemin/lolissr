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

    /** Record one failure under a row lock and return whether login is locked. */
    public function recordFailure(
        string $identifierHash,
        string $attemptedAt,
        string $windowStart,
        string $lockedUntil,
        int $maxAttempts
    ): bool {
        return $this->db->transaction(function () use (
            $identifierHash, $attemptedAt, $windowStart, $lockedUntil, $maxAttempts
        ): bool {
            // The primary key also serializes simultaneous first attempts.
            $reserved = $this->execute(
                "INSERT INTO {$this->table()} (identifier_hash, attempts, first_attempt_at, locked_until)
                VALUES (:identifier_hash, 0, :attempted_at, NULL)
                ON DUPLICATE KEY UPDATE identifier_hash = identifier_hash",
                ['identifier_hash' => $identifierHash, 'attempted_at' => $attemptedAt]
            );

            if (! $reserved)
            {
                throw new \RuntimeException('Impossible de préparer le compteur de connexion.');
            }

            $attempt = $this->fetchOne(
                "SELECT attempts, first_attempt_at, locked_until FROM {$this->table()}
                WHERE identifier_hash = :identifier_hash FOR UPDATE",
                ['identifier_hash' => $identifierHash]
            );

            if ($attempt === null)
            {
                throw new \RuntimeException('Compteur de connexion introuvable.');
            }

            if ($attempt->locked_until !== null && (string) $attempt->locked_until > $attemptedAt)
            {
                return true;
            }

            $resetWindow = (string) $attempt->first_attempt_at < $windowStart;
            $attempts = $resetWindow ? 1 : (int) $attempt->attempts + 1;
            $locked = $attempts >= $maxAttempts;

            if (! $this->update(
                [
                    'attempts' => $attempts,
                    'first_attempt_at' => $resetWindow ? $attemptedAt : (string) $attempt->first_attempt_at,
                    'locked_until' => $locked ? $lockedUntil : null,
                ],
                ['identifier_hash' => $identifierHash]
            ))
            {
                throw new \RuntimeException('Impossible de mettre à jour le compteur de connexion.');
            }

            return $locked;
        });
    }

    public function clear(string $identifierHash): bool
    {
        return $this->delete([
            'identifier_hash' => $identifierHash,
        ]);
    }
}