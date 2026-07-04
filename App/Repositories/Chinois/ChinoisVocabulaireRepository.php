<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Models\Model;
use App\Repositories\Chinois\Concerns\HasDtoMapper;

use stdClass;

final class ChinoisVocabulaireRepository extends Model
{
    use HasDtoMapper;

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

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findNotMasteredDto(): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE maitrise = 0

            ORDER BY id ASC
            "
        );

        return $this->mapResultsToDto($results);
    }

    /**
     * @return list<ChinoisVocabulaireData>
     */
    public function findByLangue(string $langue): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE langue = :langue

            ORDER BY id DESC
            ",
            [
                'langue' => trim($langue),
            ]
        );

        return $this->mapResultsToDto($results);
    }

    public function findById(int $id): ?ChinoisVocabulaireData
    {
        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE id = :id

            LIMIT 1
            ",
            [
                'id' => $id,
            ]
        );

        if ($result === null)
        {
            return null;
        }

        return $this->mapRowToDto($result);
    }

    public function toggleMaitrise(int $id): bool
    {
        $this->execute(
            "
            UPDATE {$this->table()}

            SET maitrise = NOT maitrise

            WHERE id = :id
            ",
            [
                'id' => $id,
            ]
        );

        $result = $this->fetchOne(
            "
            SELECT maitrise

            FROM {$this->table()}

            WHERE id = :id
            ",
            [
                'id' => $id,
            ]
        );

        if ($result === null)
        {
            return false;
        }

        /** @var array{maitrise?: mixed} $data */
        $data = (array) $result;

        return (bool) ($data['maitrise'] ?? false);
    }

    public function deleteVocabulaire(int $id): bool
    {
        return $this->delete(['id' => $id]);
    }

    public function updateVocabulaire(
        int $id,
        string $langue,
        string $mot,
        string $pinyin,
        string $type,
        string $traduction,
        string $exemple
    ): bool
    {
        return $this->updateById(
            $id,
            [
                'langue' => trim($langue),
                'mot' => trim($mot),
                'pinyin' => trim($pinyin),
                'type' => trim($type),
                'traduction' => trim($traduction),
                'exemple' => trim($exemple),
            ]
        );
    }

    public function markXpRewarded(int $id): bool
    {
        return $this->updateById(
            $id,
            [
                'xp_rewarded' => 1,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $data
     */
    private function updateById(int $id, array $data): bool
    {
        return $this->update($data, ['id' => $id]);
    }

    private function mapRowToDto(stdClass $row): ChinoisVocabulaireData
    {
        $exemple =
            $row->exemple !== null
                ? (string) $row->exemple
                : null;

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

            hasExemple:
                $exemple !== null
                && trim($exemple) !== '',

            masteredClass:
                $maitrise
                    ? 'active'
                    : '',

            masteredValue:
                $maitrise
                    ? '1'
                    : '0',

            masteredPressed:
                $maitrise
                    ? 'true'
                    : 'false',

            masteredLabel:
                $maitrise
                    ? 'Retirer la maîtrise'
                    : 'Marquer comme maîtrisé',
        );
    }
}