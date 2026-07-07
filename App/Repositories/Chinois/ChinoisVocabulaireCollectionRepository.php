<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;

use stdClass;

final class ChinoisVocabulaireCollectionRepository extends Model
{
    protected string $table = 'chinois_vocabulaire';

    private const SELECT_FIELDS = '
        id,
        langue,
        mot,
        pinyin,
        type,
        traduction,
        exemple,
        maitrise,
        xp_rewarded
    ';

    public function countByLangue(string $langue): int
    {
        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT COUNT(*) AS total

            FROM {$this->table()}

            WHERE langue = :langue
            ",
            [
                'langue' => trim($langue),
            ]
        );

        return (int) ($result->total ?? 0);
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findByLanguePaginated(
        string $langue,
        int $perPage,
        int $page
    ): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $offset = ($page - 1) * $perPage;

        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE langue = :langue

            ORDER BY id DESC

            LIMIT {$perPage}
            OFFSET {$offset}
            ",
            [
                'langue' => trim($langue),
            ]
        );

        /** @var list<ChinoisVocabulaireData> */
        return array_map(
            $this->mapRowToDto(...),
            $results,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapRowToDto(stdClass $row): ChinoisVocabulaireData
    {
        $exemple = (string) $row->exemple;

        $maitrise = (bool) $row->maitrise;

        return new ChinoisVocabulaireData(
            id: (int) $row->id,

            langue: (string) $row->langue,
            mot: (string) $row->mot,
            pinyin: (string) $row->pinyin,
            type: (string) $row->type,
            traduction: (string) $row->traduction,
            exemple: $exemple,

            maitrise: $maitrise,
            xpRewarded: (bool) $row->xp_rewarded,

            hasExemple: $exemple !== '',

            masteredClass: $maitrise ? 'active' : '',
            masteredValue: $maitrise ? '1' : '0',
            masteredPressed: $maitrise ? 'true' : 'false',
            masteredLabel:
                $maitrise
                    ? 'Retirer la maîtrise'
                    : 'Marquer comme maîtrisé',
        );
    }
}