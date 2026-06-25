<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Constants\UserTitle;
use App\Models\Model;
use App\Models\User;

final class UserRepository extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?User
    {
        /** @var User|null $user */
        $user = $this->fetchOne(
            "
            SELECT *
            FROM {$this->table()}
            WHERE username = :username
            LIMIT 1
            ",
            [
                'username' => trim($username),
            ],
            User::class
        );

        return $user;
    }

    public function findById(int $id): ?User
    {
        /** @var User|null $user */
        $user = $this->fetchOne(
            "
            SELECT *
            FROM {$this->table()}
            WHERE id = :id
            LIMIT 1
            ",
            [
                'id' => $id,
            ],
            User::class
        );

        return $user;
    }

    public function create(string $username, string $password): bool
    {
        return $this->insert([
            'thumbnail' => 'default',
            'extension' => 'png',
            'username' => trim($username),
            'password' => $password,
            'title' => UserTitle::EXPLORATEUR,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    public function updateLevelAndXp(int $userId, int $level, int $xp): bool
    {
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

    public function updateTitle(int $userId, string $title): bool
    {
        return $this->update(
            [
                'title' => $title,
            ],
            [
                'id' => $userId,
            ],
        );
    }

    public function avatars(): array
    {
        $path =
            dirname(__DIR__, 3)
            . '/public/images/avatars/thumbnail';

        $avatars = [];

        foreach (
            glob(
                $path . '/*.{webp,jpg,png}',
                GLOB_BRACE,
            ) ?: [] as $file
        )
        {
            $avatars[] = [
                'thumbnail' => pathinfo(
                    $file,
                    PATHINFO_FILENAME,
                ),
                'extension' => pathinfo(
                    $file,
                    PATHINFO_EXTENSION,
                ),
            ];
        }

        return $avatars;
    }

    public function updateAvatar(
        int $userId,
        string $thumbnail,
        string $extension,
    ): bool
    {
        return $this->update(
            [
                'thumbnail' => $thumbnail,
                'extension' => $extension,
            ],
            [
                'id' => $userId,
            ],
        );
    }
}
