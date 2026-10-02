<?php

declare(strict_types=1);

namespace App\Repositories\Chinois;

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Repositories\AbstractRepository;

use Framework\Support\Str;

use stdClass;

final class ChinoisGrammaireRepository extends AbstractRepository
{
    use \App\Repositories\Chinois\Concerns\ReadsFlashcardBatches;
    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function orderedTransaction(callable $callback): mixed
    {
        if ($this->db->inTransaction())
        {
            throw new \LogicException('Grammar ordering lock must precede the transaction.');
        }

        // Also protects the first insert into an empty section/category.
        $lock = 'grammar-order:' . substr(hash('sha256', \Framework\Config\DatabaseConfig::name()), 0, 40);
        $acquired = $this->fetchSingleValue('SELECT GET_LOCK(:lock_name, 10) AS acquired', 'acquired', ['lock_name' => $lock]);
        if ((int) $acquired !== 1)
        {
            throw new \RuntimeException('La grammaire est en cours de modification. Réessaie.');
        }

        try
        {
            return $this->db->transaction($callback);
        }
        finally
        {
            $this->fetchSingleValue('SELECT RELEASE_LOCK(:lock_name) AS released', 'released', ['lock_name' => $lock]);
        }
    }

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

    protected string $table = 'chinois_grammaire';

    // =========================================
    // LECTURE
    // =========================================

    /** @return array{cards: list<ChinoisGrammaireData>, total: int, offset: int} */
    public function findNotMasteredPage(int $offset): array
    {
        $page = $this->readFlashcardPage($offset);

        return [
            'cards' => array_map($this->mapRowToDto(...), $page['rows']),
            'total' => $page['total'],
            'offset' => $page['offset'],
        ];
    }

    /** @return array{cards: list<ChinoisGrammaireData>, total: int, offset: int} */
    public function findNotMasteredCursor(int $id, bool $previous): array
    {
        $page = $this->readFlashcardCursor($id, $previous);
        return ['cards' => array_map($this->mapRowToDto(...), $page['rows']),
            'total' => $page['total'], 'offset' => $page['offset']];
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findNotMasteredDto(int $startId = 0): array
    {
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE maitrise = 0 AND id >= :start_id

            ORDER BY id ASC LIMIT 50
            ",
            ['start_id' => max(0, $startId)]
        );

        return array_map($this->mapRowToDto(...), $results);
    }

    /**
     * @return list<ChinoisGrammaireData>
     */
    public function findByLevel(string $niveau, ?string $section = null): array
    {
        $sectionFilter = $section === null ? '' : 'AND section = :section AND HEX(section) = HEX(:exact_section)';
        /** @var list<stdClass> $results */
        $results = $this->fetchAll(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE niveau = :niveau
            {$sectionFilter}

            ORDER BY
                section_position ASC,
                categorie_position ASC,
                position ASC,
                id ASC
            ",
            [
                'niveau' => trim($niveau),
                ...($section === null ? [] : ['section' => $section, 'exact_section' => $section]),
            ]
        );

        return array_map($this->mapRowToDto(...), $results);
    }

    /** @return list<string> */
    public function sectionTitles(string $niveau): array
    {
        $rows = $this->fetchAll("SELECT section FROM {$this->table()}
            WHERE niveau = :niveau GROUP BY section, HEX(section)
            ORDER BY MIN(section_position), MIN(categorie_position), MIN(position), MIN(id)",
            ['niveau' => $niveau]);
        return array_map(static fn (stdClass $row): string => (string) $row->section, $rows);
    }

    public function findById(int $id): ?ChinoisGrammaireData
    {
        return $this->findOneBy([
            'id' => $id
        ]);
    }

    public function findByNiveauAndId(string $niveau, int $id): ?ChinoisGrammaireData
    {
        return $this->findOneBy([
            'id' => $id,
            'niveau' => trim($niveau)
        ]);
    }

    // =========================================
    // ÉCRITURE
    // =========================================

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
            throw new \Framework\Http\Exceptions\NotFoundException('Grammaire introuvable');
        }

        $niveau = trim($niveau);
        $section = trim($section);
        $categorie = trim($categorie);

        $sameSection = $current->niveau === $niveau
            && $current->section === $section;

        $sameLocation = $sameSection && $current->categorie === $categorie;

        $ordering = [];

        if (! $sameSection)
        {
            $ordering['section_position'] = $this->getSectionPosition($niveau, $section, $id);
        }

        if (! $sameLocation)
        {
            $ordering['categorie_position'] = $this->getCategoriePosition($niveau, $section, $categorie, $id);
        }

        $position = $sameLocation
            ? $current->position
            : $this->getNextPosition($niveau, $section, $categorie, $id);

        return $this->updateById(
            $id,
            [
                'niveau' => $niveau,
                'section' => $section,
                'categorie' => $categorie,
                ...$ordering,
                'position' => $position,
                'titre' => trim($titre),
                'structure' => trim($structure),
                'abreviation' => Str::nullableTrim($abreviation),
                'phrase' => trim($phrase),
                'pinyin' => trim($pinyin),
                'traduction' => trim($traduction),
                'explication' => trim($explication)
            ]
        );
    }

    public function deleteGrammaire(int $id): bool
    {
        return $this->deleteExistingById($id);
    }

    // =========================================
    // MAÎTRISE
    // =========================================

    public function toggleMaitrise(int $id): ?bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET maitrise = NOT maitrise

            WHERE id = :id
            ",
            [
                'id' => $id
            ]
        );

        if ($statement === false || $statement->rowCount() !== 1)
        {
            return null;
        }

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT maitrise

            FROM {$this->table()}

            WHERE id = :id

            LIMIT 1
            ",
            [
                'id' => $id
            ]
        );

        return $result !== null ? (bool) $result->maitrise : null;
    }

    // =========================================
    // POSITIONS
    // =========================================

    public function getSectionPosition(
        string $niveau,
        string $section,
        ?int $ignoreId = null
    ): int {
        $niveau = trim($niveau);
        $section = trim($section);

        $params = [
            'niveau' => $niveau,
            'section' => $section
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
        $result = $this->fetchOne($sql, $params);

        if ($result !== null)
        {
            return (int) $result->section_position;
        }

        return $this->resolveNextPosition(
            "
            SELECT MAX(section_position) AS position

            FROM {$this->table()}

            WHERE niveau = :niveau
            ",
            [
                'niveau' => $niveau
            ]
        );
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
            'categorie' => $categorie
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
        $result = $this->fetchOne($sql, $params);

        if ($result !== null)
        {
            return (int) $result->categorie_position;
        }

        return $this->resolveNextPosition(
            "
            SELECT MAX(categorie_position) AS position

            FROM {$this->table()}

            WHERE niveau = :niveau
            AND section = :section
            ",
            [
                'niveau' => $niveau,
                'section' => $section
            ]
        );
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
            'categorie' => trim($categorie)
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

        return $this->resolveNextPosition($sql, $params);
    }

    // =========================================
    // XP
    // =========================================

    public function claimXpReward(int $id): bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET xp_rewarded = 1

            WHERE id = :id
            AND xp_rewarded = 0
            ",
            [
                'id' => $id
            ]
        );

        return $statement !== false && $statement->rowCount() === 1;
    }

    // =========================================
    // HYDRATATION
    // =========================================

    /**
     * @param array<string, int|string> $criteria
     */
    private function findOneBy(array $criteria): ?ChinoisGrammaireData
    {
        $conditions = [];
        $params = [];

        foreach ($criteria as $column => $value)
        {
            $conditions[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        /** @var stdClass|null $result */
        $result = $this->fetchOne(
            "
            SELECT
                " . self::SELECT_FIELDS . "

            FROM {$this->table()}

            WHERE " . implode("\nAND ", $conditions) . "

            LIMIT 1
            ",
            $params
        );

        return $result !== null
            ? $this->mapRowToDto($result)
            : null;
    }

    private function mapRowToDto(stdClass $row): ChinoisGrammaireData
    {
        $abreviation = $row->abreviation !== null
            ? trim((string) $row->abreviation)
            : null;

        $explication = trim((string) $row->explication);
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
            hasAbreviation: $abreviation !== null && $abreviation !== '',
            hasExplication: $explication !== '',
            masteredClass: $maitrise ? 'active' : '',
            masteredValue: $maitrise ? '1' : '0',
            masteredPressed: $maitrise ? 'true' : 'false',
            masteredLabel: $maitrise
                ? 'Retirer la maîtrise'
                : 'Marquer comme maîtrisé'
        );
    }

    // =========================================
    // HELPERS
    // =========================================

    /**
     * @param array<string, mixed> $data
     */
    private function updateById(int $id, array $data): bool
    {
        return $this->update(
            $data,
            [
                'id' => $id
            ]
        );
    }

    /**
     * @param array<string, int|string> $params
     */
    private function resolveNextPosition(string $sql, array $params): int
    {
        $position = $this->fetchSingleValue(
            $sql,
            'position',
            $params,
            null
        );

        return $position === null
            ? 0
            : (int) $position + 1;
    }
}
