<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\Profile\AchievementRewards;
use App\DTO\Profile\Responses\ProfileStatsData;
use App\Models\User\User;
use App\Services\User\UserLevelService;

use Framework\Database\Database;

final readonly class AchievementXpService
{
    public function __construct(private Database $database, private UserLevelService $levels)
    {
    }

    // =================================================
    // RÉCOMPENSES
    // =================================================

    public function rewardSeries(User $user, int $completedSeries): void
    {
        $this->award($user, $this->eligible('series', AchievementRewards::SERIES, $completedSeries));
    }

    // Une fois tous les paliers de série attribués, les modifications ne donnent plus
    // de succès de série. Les anciens paliers manquants restent rattrapables.
    public function pendingSeriesTarget(User $user): int
    {
        $rewards = $this->eligible('series', AchievementRewards::SERIES, PHP_INT_MAX);
        $statement = $this->database->prepare(
            'SELECT achievement_key FROM achievement_xp_rewards WHERE user_id = ? AND achievement_key IN ('
            . implode(', ', array_fill(0, count($rewards), '?')) . ')'
        );
        $statement->execute([$user->id, ...array_keys($rewards)]);
        /** @var list<string> $claimed */
        $claimed = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $target = 0;
        foreach (AchievementRewards::SERIES as $threshold => $xp)
        {
            if (! in_array('series_' . $threshold, $claimed, true)) $target = $threshold;
        }
        return $target;
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
            ...$this->eligible('series', AchievementRewards::SERIES, $completedSeries)
        ], $baseXp);
    }

    /** @param callable(): ProfileStatsData $loadStats */
    public function reconcile(User $user, callable $loadStats): void
    {
        $this->database->transaction(function () use ($user, $loadStats): void
        {
            $lock = $this->database->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
            $lock->execute([$user->id]);
            if ($lock->fetchColumn() === false) throw new \RuntimeException('Utilisateur introuvable.');
            $stats = $loadStats();
            $expected = $this->expectedRewards($stats);
            $audit = $this->audit($user, $stats);
            $query = $this->database->prepare('SELECT achievement_key, xp FROM achievement_xp_rewards WHERE user_id = ? FOR UPDATE');
            $query->execute([$user->id]);
            $delete = $this->database->prepare('DELETE FROM achievement_xp_rewards WHERE user_id = ? AND achievement_key = ?');
            $updateReward = $this->database->prepare('UPDATE achievement_xp_rewards SET xp = ? WHERE user_id = ? AND achievement_key = ?');
            $missing = $expected;
            /** @var list<array{achievement_key: string, xp: int|string}> $rows */
            $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row)
            {
                $key = $row['achievement_key'];
                unset($missing[$key]);
                if (!isset($expected[$key])) $delete->execute([$user->id, $key]);
                elseif ((int) $row['xp'] !== $expected[$key]) $updateReward->execute([$expected[$key], $user->id, $key]);
            }
            $insert = $this->database->prepare('INSERT INTO achievement_xp_rewards (user_id, achievement_key, xp) VALUES (?, ?, ?)');
            foreach ($missing as $key => $xp) $insert->execute([$user->id, $key, $xp]);
            $update = $this->database->prepare('UPDATE users SET level = ?, xp = ? WHERE id = ?');
            $update->execute([$audit['expectedLevel'], $audit['expectedXp'], $user->id]);
            $previous = [$user->level, $user->xp];
            $this->database->onRollback(static function () use ($user, $previous): void
            { [$user->level, $user->xp] = $previous; });
            $user->level = $audit['expectedLevel'];
            $user->xp = $audit['expectedXp'];
        });
    }

    // =================================================
    // AUDIT ET TOTAL DES XP
    // =================================================

    /** @return array{missing: array<string, int>, issues: list<string>, expectedTotal: int, expectedLevel: int, expectedXp: int} */
    public function audit(User $user, ProfileStatsData $stats): array
    {
        $expected = $this->expectedRewards($stats);
        $catalog = [];
        foreach ([
            'tomes' => AchievementRewards::TOMES,
            'series' => AchievementRewards::SERIES,
            'artbooks' => AchievementRewards::ARTBOOKS,
            'figurines' => AchievementRewards::FIGURINES,
            'nendoroids' => AchievementRewards::NENDOROIDS,
            'peluches' => AchievementRewards::PELUCHES,
            'vocabulary' => AchievementRewards::VOCABULARY,
            'grammar' => AchievementRewards::GRAMMAR
        ] as $category => $amounts)
        {
            $catalog += $this->eligible($category, $amounts, PHP_INT_MAX);
        }
        $statement = $this->database->prepare('SELECT achievement_key, xp FROM achievement_xp_rewards WHERE user_id = ?');
        $statement->execute([$user->id]);
        /** @var list<array{achievement_key: string, xp: int|string}> $rows */
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $missing = $expected;
        $issues = [];
        foreach ($rows as $row)
        {
            $key = $row['achievement_key'];
            unset($missing[$key]);
            if (!isset($catalog[$key]))
            {
                $issues[] = "$key : récompense inconnue (" . $row['xp'] . ' XP).';
                continue;
            }
            if ((int) $row['xp'] !== $catalog[$key])
            {
                $issues[] = "$key : montant enregistré " . $row['xp'] . ', attendu ' . $catalog[$key] . ' XP.';
            }
            if (!isset($expected[$key]))
            {
                $issues[] = "$key : récompense non justifiée par les statistiques actuelles.";
            }
        }
        $total = $stats->totalXp - $stats->achievementXp + array_sum($expected);
        $level = 1;
        $xp = $total;
        while ($xp >= $this->levels->xpRequiredForLevel($level))
        {
            $xp -= $this->levels->xpRequiredForLevel($level);
            $level++;
        }
        if ($user->level !== $level || $user->xp !== $xp)
        {
            $issues[] = "Compte : niveau {$user->level}, {$user->xp} XP ; attendu niveau $level, $xp XP (total calculé : $total).";
        }
        return ['missing' => $missing, 'issues' => $issues, 'expectedTotal' => $total, 'expectedLevel' => $level, 'expectedXp' => $xp];
    }

    public function totalForUser(User $user): int
    {
        $statement = $this->database->prepare('SELECT COALESCE(SUM(xp), 0) FROM achievement_xp_rewards WHERE user_id = ?');
        $statement->execute([$user->id]);
        return (int) $statement->fetchColumn();
    }

    // =================================================
    // CALCUL ET ATTRIBUTION DES XP
    // =================================================

    /** @return array<string, int> */
    private function expectedRewards(ProfileStatsData $stats): array
    {
        return [
            ...$this->eligible('tomes', AchievementRewards::TOMES, $stats->readTomes),
            ...$this->eligible('series', AchievementRewards::SERIES, $stats->completedSeries),
            ...$this->eligible('artbooks', AchievementRewards::ARTBOOKS, $stats->readArtbooks),
            ...$this->eligible('figurines', AchievementRewards::FIGURINES, $stats->figurinesCollected),
            ...$this->eligible('nendoroids', AchievementRewards::NENDOROIDS, $stats->nendoroidsCollected),
            ...$this->eligible('peluches', AchievementRewards::PELUCHES, $stats->peluchesCollected),
            ...$this->eligible('vocabulary', AchievementRewards::VOCABULARY, $stats->vocabularyLearned),
            ...$this->eligible('grammar', AchievementRewards::GRAMMAR, $stats->grammarLearned)
        ];
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

        $this->levels->addComputedXp($user, function () use ($user, $rewards, $baseXp): int
        {
            if ($rewards === []) return $baseXp;
            // La lecture verrouillée voit aussi les récompenses validées par une requête attendue.
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
}
