<?php

declare(strict_types=1);

namespace App\Repositories\Profile;

use App\Repositories\AbstractRepository;

final class ProfileStatsRepository extends AbstractRepository
{
    /** @return array<string, int> */
    public function summary(?int $userId): array
    {
        // Chaque agrégat retourne une ligne, même pour une table vide. Les jointures croisées
        // combinent ces résumés, jamais les lignes individuelles des collections.
        $parts = ["(SELECT COUNT(CASE WHEN lu = 1 THEN 1 END) AS manga_read,
            COUNT(CASE WHEN xp_read_rewarded = 1 THEN 1 END) AS manga_rewarded_tomes,
            COUNT(DISTINCT CASE WHEN xp_series_rewarded = 1 THEN slug END) AS manga_rewarded_series
            FROM manga) manga_stats"];
        foreach ([
            'artbook' => ['lu', 'xp_read_rewarded'],
            'figurine' => ['collect', 'collect_rewarded'],
            'nendoroid' => ['collect', 'collect_rewarded'],
            'peluche' => ['collect', 'collect_rewarded'],
            'chinois_vocabulaire' => ['maitrise', 'xp_rewarded'],
            'chinois_grammaire' => ['maitrise', 'xp_rewarded'],
        ] as $table => [$status, $reward])
        {
            $parts[] = "(SELECT COUNT(CASE WHEN $status = 1 THEN 1 END) AS {$table}_count,
                COUNT(CASE WHEN $reward = 1 THEN 1 END) AS {$table}_rewarded FROM $table) {$table}_stats";
        }
        $parts[] = "(SELECT COUNT(*) AS completed_series FROM (
            SELECT slug FROM manga GROUP BY slug HAVING COUNT(*) = SUM(lu)
            AND MAX(CASE WHEN numero = 1 AND statut = 'termine' THEN 1 ELSE 0 END) = 1
        ) completed) series_stats";
        $parts[] = $userId === null
            ? '(SELECT 0 AS achievement_xp) achievements'
            : '(SELECT COALESCE(SUM(xp), 0) AS achievement_xp FROM achievement_xp_rewards WHERE user_id = :user_id) achievements';
        $row = $this->fetchOne('SELECT * FROM ' . implode(' CROSS JOIN ', $parts),
            $userId === null ? [] : ['user_id' => $userId]);

        return array_map(static fn (mixed $value): int => (int) $value, (array) $row);
    }
}
