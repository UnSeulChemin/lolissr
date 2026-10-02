<?php

declare(strict_types=1);

namespace App\Repositories\Profile;

use App\DTO\Profile\Responses\ProfileUnlockStatsData;
use App\Repositories\AbstractRepository;

final class ProfileUnlockStatsRepository extends AbstractRepository
{
    private const COUNTERS = [
        'readTomes' => 'SELECT COUNT(*) FROM manga WHERE lu = 1',
        'readArtbooks' => 'SELECT COUNT(*) FROM artbook WHERE lu = 1',
        'figurinesCollected' => 'SELECT COUNT(*) FROM figurine WHERE collect = 1',
        'nendoroidsCollected' => 'SELECT COUNT(*) FROM nendoroid WHERE collect = 1',
        'peluchesCollected' => 'SELECT COUNT(*) FROM peluche WHERE collect = 1',
        'vocabularyLearned' => 'SELECT COUNT(*) FROM chinois_vocabulaire WHERE maitrise = 1',
        'grammarLearned' => 'SELECT COUNT(*) FROM chinois_grammaire WHERE maitrise = 1',
    ];

    public function forTitles(): ProfileUnlockStatsData
    {
        return $this->counts([
            'readTomes', 'readArtbooks', 'figurinesCollected', 'nendoroidsCollected',
            'vocabularyLearned', 'grammarLearned',
        ], true);
    }

    public function forBanners(): ProfileUnlockStatsData
    {
        return $this->counts([
            'readTomes', 'nendoroidsCollected', 'peluchesCollected', 'vocabularyLearned', 'grammarLearned',
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
            $select[] = '(' . self::COUNTERS[$counter] . ') AS ' . $counter;
        }
        if ($includeSeries)
        {
            $select[] = "(SELECT COUNT(*) FROM (
                SELECT slug FROM manga GROUP BY slug
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
            grammarLearned: (int) ($row->grammarLearned ?? 0),
        );
    }
}
