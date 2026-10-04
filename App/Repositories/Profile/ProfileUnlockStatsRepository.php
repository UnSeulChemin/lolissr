<?php

declare(strict_types=1);

namespace App\Repositories\Profile;

use App\DTO\Profile\Responses\ProfileUnlockStatsData;
use App\Repositories\AbstractRepository;

final class ProfileUnlockStatsRepository extends AbstractRepository
{
    private const COUNTERS = [
        'readTomes' => ['manga', 'lu'],
        'readArtbooks' => ['artbook', 'lu'],
        'figurinesCollected' => ['figurine', 'collect'],
        'nendoroidsCollected' => ['nendoroid', 'collect'],
        'peluchesCollected' => ['peluche', 'collect'],
        'vocabularyLearned' => ['chinois_vocabulaire', 'maitrise'],
        'grammarLearned' => ['chinois_grammaire', 'maitrise']
    ];

    public function forTitles(): ProfileUnlockStatsData
    {
        return $this->counts([
            'readTomes', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected',
            'vocabularyLearned', 'grammarLearned'
        ], true);
    }

    public function forBanners(): ProfileUnlockStatsData
    {
        return $this->counts([
            'readTomes', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned'
        ]);
    }

    public function forFrames(): ProfileUnlockStatsData
    {
        return $this->counts(array_keys(self::COUNTERS));
    }

    public function forAchievements(): ProfileUnlockStatsData
    {
        return $this->counts(array_keys(self::COUNTERS), true);
    }

    /** @param list<key-of<self::COUNTERS>> $counters */
    private function counts(array $counters, bool $includeSeries = false): ProfileUnlockStatsData
    {
        $select = [];
        foreach ($counters as $counter)
        {
            [$table, $status] = self::COUNTERS[$counter];
            $select[] = "(SELECT COUNT(*) FROM {$this->ownedTable($table)} WHERE {$status} = 1) AS {$counter}";
        }
        if ($includeSeries)
        {
            $select[] = "(SELECT COUNT(*) FROM (
                SELECT slug FROM {$this->ownedTable('manga')} GROUP BY slug
                HAVING COUNT(*) = SUM(lu)
                AND MAX(CASE WHEN numero = 1 AND statut = 'termine' THEN 1 ELSE 0 END) = 1
            ) completed) AS completedSeries";
        }
        $row = $this->fetchOne('SELECT ' . implode(', ', $select));

        return new ProfileUnlockStatsData(
            readTomes: (int) ($row->readTomes ?? 0),
            completedSeries: (int) ($row->completedSeries ?? 0),
            readArtbooks: (int) ($row->readArtbooks ?? 0),
            figurinesCollected: (int) ($row->figurinesCollected ?? 0),
            nendoroidsCollected: (int) ($row->nendoroidsCollected ?? 0),
            peluchesCollected: (int) ($row->peluchesCollected ?? 0),
            vocabularyLearned: (int) ($row->vocabularyLearned ?? 0),
            grammarLearned: (int) ($row->grammarLearned ?? 0)
        );
    }
}
