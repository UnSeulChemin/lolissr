<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Services\User\UserLevelService;
use Framework\Database\Database;

final readonly class AchievementXpService
{
    public const FIRST_TOME_XP = 50;
    public const FIGURINE_REWARDS = [1 => 2000, 4 => 8000, 8 => 16000];
    public const ARTBOOK_REWARDS = [1 => 500, 10 => 5000, 25 => 12500];
    public const SERIES_REWARDS = [1 => 50, 10 => 500, 25 => 1250, 50 => 2500];
    public const TOME_REWARDS = [1 => self::FIRST_TOME_XP, 10 => 500, 25 => 1250, 50 => 2500, 100 => 5000, 200 => 10000];

    public function __construct(
        private Database $database,
        private UserRepository $users,
        private UserLevelService $levels,
    ) {
    }

    public function rewardTomes(User $user, int $readTomes): void
    {
        foreach (self::TOME_REWARDS as $target => $xp)
        {
            if ($readTomes >= $target)
            {
                $this->award($user, 'tomes_' . $target, $xp);
            }
        }
    }

    public function rewardSeries(User $user, int $completedSeries): void
    {
        foreach (self::SERIES_REWARDS as $target => $xp)
        {
            if ($completedSeries >= $target)
            {
                $this->award($user, 'series_' . $target, $xp);
            }
        }
    }

    public function rewardArtbooks(User $user, int $readArtbooks): void
    {
        foreach (self::ARTBOOK_REWARDS as $target => $xp)
        {
            if ($readArtbooks >= $target)
            {
                $this->award($user, 'artbooks_' . $target, $xp);
            }
        }
    }

    public function rewardFigurines(User $user, int $collected): void
    {
        foreach (self::FIGURINE_REWARDS as $target => $xp)
        {
            if ($collected >= $target)
            {
                $this->award($user, 'figurines_' . $target, $xp);
            }
        }
    }

    private function award(User $user, string $key, int $xp): void
    {
        $award = function () use ($user, $key, $xp): void {
            // Serialize claims for this user, including simultaneous requests.
            if ($this->users->lockLevelAndXp($user->id) === null)
            {
                throw new \RuntimeException('Utilisateur introuvable.');
            }
            $check = $this->database->prepare('SELECT xp FROM achievement_xp_rewards WHERE user_id = ? AND achievement_key = ?');
            $check->execute([$user->id, $key]);
            if ($check->fetchColumn() !== false)
            {
                return;
            }
            $insert = $this->database->prepare('INSERT INTO achievement_xp_rewards (user_id, achievement_key, xp) VALUES (?, ?, ?)');
            $insert->execute([$user->id, $key, $xp]);
            $this->levels->addXp($user, $xp);
        };
        if ($this->database->inTransaction())
        {
            $award();
        }
        else
        {
            $this->database->transaction($award);
        }
    }

    public function totalForUser(User $user): int
    {
        $statement = $this->database->prepare('SELECT COALESCE(SUM(xp), 0) FROM achievement_xp_rewards WHERE user_id = ?');
        $statement->execute([$user->id]);
        return (int) $statement->fetchColumn();
    }
}
