<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\AchievementRewards;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Services\User\UserLevelService;
use Framework\Database\Database;

final readonly class AchievementXpService
{
    public function __construct(
        private Database $database,
        private UserRepository $users,
        private UserLevelService $levels,
    ) {
    }

    public function rewardTomes(User $user, int $readTomes): void
    {
        foreach (AchievementRewards::TOMES as $target => $xp)
        {
            if ($readTomes >= $target)
            {
                $this->award($user, 'tomes_' . $target, $xp);
            }
        }
    }

    public function rewardSeries(User $user, int $completedSeries): void
    {
        foreach (AchievementRewards::SERIES as $target => $xp)
        {
            if ($completedSeries >= $target)
            {
                $this->award($user, 'series_' . $target, $xp);
            }
        }
    }

    public function rewardArtbooks(User $user, int $readArtbooks): void
    {
        foreach (AchievementRewards::ARTBOOKS as $target => $xp)
        {
            if ($readArtbooks >= $target)
            {
                $this->award($user, 'artbooks_' . $target, $xp);
            }
        }
    }

    public function rewardFigurines(User $user, int $collected): void
    {
        foreach (AchievementRewards::FIGURINES as $target => $xp)
        {
            if ($collected >= $target)
            {
                $this->award($user, 'figurines_' . $target, $xp);
            }
        }
    }

    public function rewardNendoroids(User $user, int $collected): void
    {
        foreach (AchievementRewards::NENDOROIDS as $target => $xp)
        {
            if ($collected >= $target)
            {
                $this->award($user, 'nendoroids_' . $target, $xp);
            }
        }
    }

    public function rewardPeluches(User $user, int $collected): void
    {
        foreach (AchievementRewards::PELUCHES as $target => $xp)
        {
            if ($collected >= $target)
            {
                $this->award($user, 'peluches_' . $target, $xp);
            }
        }
    }

    public function rewardVocabulary(User $user, int $mastered): void
    {
        foreach (AchievementRewards::VOCABULARY as $target => $xp)
        {
            if ($mastered >= $target)
            {
                $this->award($user, 'vocabulary_' . $target, $xp);
            }
        }
    }

    public function rewardGrammar(User $user, int $mastered): void
    {
        foreach (AchievementRewards::GRAMMAR as $target => $xp)
        {
            if ($mastered >= $target)
            {
                $this->award($user, 'grammar_' . $target, $xp);
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
