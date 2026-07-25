<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Models\Model;

use Framework\Support\Str;

use stdClass;

final class ChinoisGrammaireRepository extends Model
{
    protected string $table = 'chinois_grammaire';

    private const SELECT_FIELDS = '
        id,
        niveau,
        section,
        categorie,
        titre,
        structure,
        abreviation,
        phrase,
        pinyin,
        traduction,
        explication,
        position,
        maitrise,
        xp_rewarded
    ';

    /*
    |--------------------------------------------------------------------------
    | LECTURE
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<ChinoisGrammaireData>
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

        return array_map(
            $this->mapRowToDto(...),
            $results
        );
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findByLevel(string $niveau): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE niveau = :niveau

            ORDER BY
                section_position ASC,
                categorie_position ASC,
                position ASC,
                id ASC
            ",
            [
                'niveau' => trim($niveau),
            ]
        );

        return array_map(
            $this->mapRowToDto(...),
            $results
        );
    }

    public function findById(int $id): ?ChinoisGrammaireData
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

    /*
    |--------------------------------------------------------------------------
    | ÉCRITURE
    |--------------------------------------------------------------------------
    */

    public function toggleMaitrise(int $id): ?bool
    {
        if (! $this->existsById($id))
        {
            return null;
        }

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

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT maitrise

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

        return (bool) $result->maitrise;
    }

    public function deleteGrammaire(int $id): bool
    {
        return $this->delete([
            'id' => $id,
        ]);
    }

    public function updateGrammaire(
        int $id,
        string $niveau,
        string $titre,
        string $structure,
        ?string $abreviation,
        string $phrase,
        string $pinyin,
        string $traduction,
        string $explication,
        string $section,
        string $categorie
    ): bool {
        $current = $this->findById($id);

        if ($current === null)
        {
            return false;
        }

        $niveau = trim($niveau);
        $section = trim($section);
        $categorie = trim($categorie);

        $sameLocation =
            $current->niveau === $niveau
            && $current->section === $section
            && $current->categorie === $categorie;

        $position = $sameLocation
            ? $current->position
            : $this->getNextPosition(
                $niveau,
                $section,
                $categorie,
                $id
            );

        return $this->updateById(
            $id,
            [
                'niveau' => $niveau,

                'section' => $section,
                'section_position' => $this->getSectionPosition(
                    $niveau,
                    $section,
                    $id
                ),

                'categorie' => $categorie,
                'categorie_position' => $this->getCategoriePosition(
                    $niveau,
                    $section,
                    $categorie,
                    $id
                ),

                'position' => $position,

                'titre' => trim($titre),
                'structure' => trim($structure),
                'abreviation' => Str::nullableTrim($abreviation),

                'phrase' => trim($phrase),
                'pinyin' => trim($pinyin),
                'traduction' => trim($traduction),
                'explication' => trim($explication),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POSITIONS
    |--------------------------------------------------------------------------
    */

    public function getSectionPosition(
        string $niveau,
        string $section,
        ?int $ignoreId = null
    ): int {
        $niveau = trim($niveau);
        $section = trim($section);

        $params = [
            'niveau' => $niveau,
            'section' => $section,
        ];

        $sql = "
            SELECT section_position

            FROM {$this->table()}

            WHERE niveau = :niveau
            AND section = :section
        ";

        if ($ignoreId !== null)
        {
            $sql .= "\nAND id <> :id";

            $params['id'] = $ignoreId;
        }

        $sql .= "\nLIMIT 1";

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            $sql,
            $params
        );

        if ($result !== null)
        {
            return (int) $result->section_position;
        }

        /** @var stdClass|null $max */
        $max = $this->fetchOne(
            "
            SELECT MAX(section_position) AS position

            FROM {$this->table()}

            WHERE niveau = :niveau
            ",
            [
                'niveau' => $niveau,
            ]
        );

        return (int) (($max->position ?? -1) + 1);
    }

    public function getCategoriePosition(
        string $niveau,
        string $section,
        string $categorie,
        ?int $ignoreId = null
    ): int {
        $niveau = trim($niveau);
        $section = trim($section);
        $categorie = trim($categorie);

        $params = [
            'niveau' => $niveau,
            'section' => $section,
            'categorie' => $categorie,
        ];

        $sql = "
            SELECT categorie_position

            FROM {$this->table()}

            WHERE niveau = :niveau
            AND section = :section
            AND categorie = :categorie
        ";

        if ($ignoreId !== null)
        {
            $sql .= "\nAND id <> :id";

            $params['id'] = $ignoreId;
        }

        $sql .= "\nLIMIT 1";

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            $sql,
            $params
        );

        if ($result !== null)
        {
            return (int) $result->categorie_position;
        }

        /** @var stdClass|null $max */
        $max = $this->fetchOne(
            "
            SELECT MAX(categorie_position) AS position

            FROM {$this->table()}

            WHERE niveau = :niveau
            AND section = :section
            ",
            [
                'niveau' => $niveau,
                'section' => $section,
            ]
        );

        return (int) (($max->position ?? -1) + 1);
    }

    public function getNextPosition(
        string $niveau,
        string $section,
        string $categorie,
        ?int $ignoreId = null
    ): int {
        $params = [
            'niveau' => trim($niveau),
            'section' => trim($section),
            'categorie' => trim($categorie),
        ];

        $sql = "
            SELECT MAX(position) AS position

            FROM {$this->table()}

            WHERE niveau = :niveau
            AND section = :section
            AND categorie = :categorie
        ";

        if ($ignoreId !== null)
        {
            $sql .= "\nAND id <> :id";

            $params['id'] = $ignoreId;
        }

        /** @var stdClass|null $max */
        $max = $this->fetchOne(
            $sql,
            $params
        );

        return (int) (($max->position ?? -1) + 1);
    }

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    public function isXpRewarded(int $id): bool
    {
        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT xp_rewarded

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
            return true;
        }

        return (bool) $result->xp_rewarded;
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

    private function existsById(int $id): bool
    {
        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT 1

            FROM {$this->table()}

            WHERE id = :id

            LIMIT 1
            ",
            [
                'id' => $id,
            ]
        );

        return $result !== null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateById(
        int $id,
        array $data
    ): bool {
        return $this->update(
            $data,
            [
                'id' => $id,
            ]
        );
    }

    private function mapRowToDto(
        stdClass $row
    ): ChinoisGrammaireData {
        $abreviation = $row->abreviation !== null
            ? (string) $row->abreviation
            : null;

        $explication = $row->explication !== null
            ? (string) $row->explication
            : null;

        $maitrise = (bool) $row->maitrise;

        return new ChinoisGrammaireData(
            id: (int) $row->id,

            niveau: (string) $row->niveau,

            section: (string) $row->section,
            categorie: (string) $row->categorie,

            titre: (string) $row->titre,
            structure: (string) $row->structure,
            abreviation: $abreviation,

            phrase: (string) $row->phrase,
            pinyin: (string) $row->pinyin,
            traduction: (string) $row->traduction,
            explication: $explication,

            position: (int) $row->position,

            maitrise: $maitrise,
            xpRewarded: (bool) $row->xp_rewarded,

            hasAbreviation:
                $abreviation !== null
                && $abreviation !== '',

            hasExplication:
                $explication !== null
                && $explication !== '',

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
                    : 'Marquer comme maîtrisé'
        );
    }
}