<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Models\Model;
use App\Models\User;

final class UserRepository extends Model
{
    protected string $table = 'users';

    public function findByUsername(
        string $username,
    ): ?User {

        /** @var User|null $user */
        $user = $this->fetchOne(
            "
            SELECT *
            FROM {$this->getTable()}
            WHERE username = :username
            LIMIT 1
            ",
            [
                'username' => trim($username),
            ],
            User::class,
        );

        return $user;
    }

    public function findById(
        int $id,
    ): ?User {

        /** @var User|null $user */
        $user = $this->fetchOne(
            "
            SELECT *
            FROM {$this->getTable()}
            WHERE id = :id
            LIMIT 1
            ",
            [
                'id' => $id,
            ],
            User::class,
        );

        return $user;
    }

    public function create(
        string $username,
        string $password,
    ): bool {

        return $this->insert([
            'username' => trim($username),
            'password' => $password,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    public function updateLevelAndXp(
        int $userId,
        int $level,
        int $xp,
    ): bool {

        return $this->update(
            [
                'level' => $level,
                'xp' => $xp,
            ],
            [
                'id' => $userId,
            ],
        );
    }
}