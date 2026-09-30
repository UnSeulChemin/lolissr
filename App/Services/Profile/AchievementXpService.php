<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\AchievementRewards;
use App\Models\User;
use App\DTO\Profile\ProfileStatsData;
use App\Services\User\UserLevelService;
use Framework\Database\Database;

final readonly class AchievementXpService
{
    public function __construct(
        private Database $database,
        private UserLevelService $levels,
    ) {
    }

    public function rewardSeries(User $user, int $completedSeries): void
    {
        $this->award($user, $this->eligible('series', AchievementRewards::SERIES, $completedSeries));
    }

    public function rewardArtbooks(User $user, int $readArtbooks, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('artbooks', AchievementRewards::ARTBOOKS, $readArtbooks), $baseXp);
    }

    public function rewardFigurines(User $user, int $collected, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('figurines', AchievementRewards::FIGURINES, $collected), $baseXp);
    }

    public function rewardNendoroids(User $user, int $collected, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('nendoroids', AchievementRewards::NENDOROIDS, $collected), $baseXp);
    }

    public function rewardPeluches(User $user, int $collected, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('peluches', AchievementRewards::PELUCHES, $collected), $baseXp);
    }

    public function rewardVocabulary(User $user, int $mastered, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('vocabulary', AchievementRewards::VOCABULARY, $mastered), $baseXp);
    }

    public function rewardGrammar(User $user, int $mastered, int $baseXp = 0): void
    {
        $this->award($user, $this->eligible('grammar', AchievementRewards::GRAMMAR, $mastered), $baseXp);
    }

    public function rewardManga(User $user, int $readTomes, int $completedSeries, int $baseXp = 0): void
    {
        $this->award($user, [
            ...$this->eligible('tomes', AchievementRewards::TOMES, $readTomes),
            ...$this->eligible('series', AchievementRewards::SERIES, $completedSeries),
        ], $baseXp);
    }

    public function rewardAll(User $user, ProfileStatsData $stats): void
    {
        $this->award($user, [
            ...$this->eligible('tomes', AchievementRewards::TOMES, $stats->readTomes),
            ...$this->eligible('series', AchievementRewards::SERIES, $stats->completedSeries),
            ...$this->eligible('artbooks', AchievementRewards::ARTBOOKS, $stats->readArtbooks),
            ...$this->eligible('figurines', AchievementRewards::FIGURINES, $stats->figurinesCollected),
            ...$this->eligible('nendoroids', AchievementRewards::NENDOROIDS, $stats->nendoroidsCollected),
            ...$this->eligible('peluches', AchievementRewards::PELUCHES, $stats->peluchesCollected),
            ...$this->eligible('vocabulary', AchievementRewards::VOCABULARY, $stats->vocabularyLearned),
            ...$this->eligible('grammar', AchievementRewards::GRAMMAR, $stats->grammarLearned),
        ]);
    }

    /** @param array<int, int> $rewards
     *  @return array<string, int>
     */
    private function eligible(string $category, array $rewards, int $count): array
    {
        $eligible = [];
        foreach ($rewards as $target => $xp)
        {
            if ($count >= $target) $eligible[$category . '_' . $target] = $xp;
        }
        return $eligible;
    }

    /** @param array<string, int> $rewards */
    private function award(User $user, array $rewards, int $baseXp = 0): void
    {
        if ($baseXp < 0) throw new \InvalidArgumentException('Base XP must not be negative.');
        if ($rewards === [] && $baseXp === 0) return;

        $this->levels->addComputedXp($user, function () use ($user, $rewards, $baseXp): int {
            if ($rewards === []) return $baseXp;
            // A current locking read also sees claims committed by a request we waited for.
            $placeholders = implode(', ', array_fill(0, count($rewards), '?'));
            $check = $this->database->prepare(
                'SELECT achievement_key FROM achievement_xp_rewards WHERE user_id = ?'
                . ' AND achievement_key IN (' . $placeholders . ') FOR UPDATE'
            );
            $check->execute([$user->id, ...array_keys($rewards)]);
            /** @var list<string> $claimed */
            $claimed = $check->fetchAll(\PDO::FETCH_COLUMN);
            $missing = array_diff_key($rewards, array_fill_keys($claimed, true));
            if ($missing === []) return $baseXp;

            $values = [];
            foreach ($missing as $key => $xp)
            {
                array_push($values, $user->id, $key, $xp);
            }
            $insert = $this->database->prepare(
                'INSERT INTO achievement_xp_rewards (user_id, achievement_key, xp) VALUES '
                . implode(', ', array_fill(0, count($missing), '(?, ?, ?)'))
            );
            $insert->execute($values);
            return $baseXp + array_sum($missing);
        });
    }
    public function totalForUser(User $user): int
    {
        $statement = $this->database->prepare('SELECT COALESCE(SUM(xp), 0) FROM achievement_xp_rewards WHERE user_id = ?');
        $statement->execute([$user->id]);
        return (int) $statement->fetchColumn();
    }
}
