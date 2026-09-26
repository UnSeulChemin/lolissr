<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Constants\UserTitle;
use App\Models\Model;
use App\Models\User;

final class UserRepository extends Model
{
    protected string $table = 'users';

    public function lockLevelAndXp(int $id): ?User
    {
        if (! $this->db->inTransaction())
        {
            throw new \LogicException('XP updates require an active transaction.');
        }
        return $this->fetchOne(
            "SELECT id, level, xp FROM {$this->table()} WHERE id = :id FOR UPDATE",
            ['id' => $id],
            User::class
        );
    }

    // =========================================
    // RECHERCHE
    // =========================================

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
            ['username' => trim($username)],
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
            ['id' => $id],
            User::class
        );

        return $user;
    }

    // =========================================
    // CRÉATION
    // =========================================

    public function create(string $username, string $password): bool
    {
        return $this->insert([
            'avatar' => 'default',
            'avatar_extension' => 'png',
            'banner' => 'default',
            'banner_extension' => 'png',
            'frame' => 'default',
            'frame_extension' => 'png',
            'username' => trim($username),
            'password' => $password,
            'title' => UserTitle::EXPLORATEUR,
            'level' => 1,
            'xp' => 0,
        ]);
    }

    // =========================================
    // AUTHENTIFICATION
    // =========================================

    public function updatePasswordHash(int $userId, string $passwordHash): bool
    {
        return $this->update(
            ['password' => $passwordHash],
            ['id' => $userId]
        );
    }

    // =========================================
    // PROFIL
    // =========================================

    public function updateLevelAndXp(int $userId, int $level, int $xp): bool
    {
        return $this->update(
            [
                'level' => $level,
                'xp' => $xp,
            ],
            ['id' => $userId]
        );
    }

    public function updateTitle(int $userId, string $title): bool
    {
        return $this->update(
            ['title' => $title],
            ['id' => $userId]
        );
    }

    public function updateAvatar(int $userId, string $avatar, string $avatarExtension): bool
    {
        return $this->update(
            [
                'avatar' => $avatar,
                'avatar_extension' => $avatarExtension,
            ],
            ['id' => $userId]
        );
    }

    public function updateBanner(int $userId, string $banner, string $bannerExtension): bool
    {
        return $this->update(
            [
                'banner' => $banner,
                'banner_extension' => $bannerExtension,
            ],
            ['id' => $userId]
        );
    }

    public function updateFrame(int $userId, string $frame, string $frameExtension): bool
    {
        return $this->update(
            [
                'frame' => $frame,
                'frame_extension' => $frameExtension,
            ],
            ['id' => $userId]
        );
    }
}
