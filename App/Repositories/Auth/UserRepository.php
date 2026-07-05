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

    /**
     * @return list<array{
     *     avatar: string,
     *     avatar_extension: string
     * }>
     */
    public function avatars(): array
    {
        $path =
            dirname(__DIR__, 3)
            . '/public/images/avatar/thumbnail';

        $avatars = [];

        $files = glob(
            $path . '/*.{webp,jpg,png}',
            GLOB_BRACE,
        );

        if ($files === false)
        {
            return [];
        }

        foreach ($files as $file)
        {
            $avatars[] = [
                'avatar' => pathinfo(
                    $file,
                    PATHINFO_FILENAME,
                ),
                'avatar_extension' => pathinfo(
                    $file,
                    PATHINFO_EXTENSION,
                ),
            ];
        }

        return $avatars;
    }

    /**
     * @return list<array{
     *     banner: string,
     *     banner_extension: string
     * }>
     */
    public function banners(): array
    {
        $path =
            dirname(__DIR__, 3)
            . '/public/images/banner/thumbnail';

        $banners = [];

        $files = glob(
            $path . '/*.{webp,jpg,png}',
            GLOB_BRACE,
        );

        if ($files === false)
        {
            return [];
        }

        foreach ($files as $file)
        {
            $banners[] = [
                'banner' => pathinfo(
                    $file,
                    PATHINFO_FILENAME,
                ),
                'banner_extension' => pathinfo(
                    $file,
                    PATHINFO_EXTENSION,
                ),
            ];
        }

        return $banners;
    }

    /**
     * @return list<array{
     *     frame: string,
     *     frame_extension: string
     * }>
     */
    public function frames(): array
    {
        $path =
            dirname(__DIR__, 3)
            . '/public/images/frame/thumbnail';

        $frames = [];

        $files = glob(
            $path . '/*.{webp,jpg,png}',
            GLOB_BRACE,
        );

        if ($files === false)
        {
            return [];
        }

        foreach ($files as $file)
        {
            $frames[] = [
                'frame' => pathinfo(
                    $file,
                    PATHINFO_FILENAME,
                ),
                'frame_extension' => pathinfo(
                    $file,
                    PATHINFO_EXTENSION,
                ),
            ];
        }

        return $frames;
    }

    public function updateAvatar(
        int $userId,
        string $avatar,
        string $avatarExtension,
    ): bool
    {
        return $this->update(
            [
                'avatar' => $avatar,
                'avatar_extension' => $avatarExtension,
            ],
            [
                'id' => $userId,
            ],
        );
    }

    public function updateBanner(
        int $userId,
        string $banner,
        string $bannerExtension,
    ): bool
    {
        return $this->update(
            [
                'banner' => $banner,
                'banner_extension' => $bannerExtension,
            ],
            [
                'id' => $userId,
            ],
        );
    }

    public function updateFrame(
        int $userId,
        string $frame,
        string $frameExtension,
    ): bool
    {
        return $this->update(
            [
                'frame' => $frame,
                'frame_extension' => $frameExtension,
            ],
            [
                'id' => $userId,
            ],
        );
    }
}
