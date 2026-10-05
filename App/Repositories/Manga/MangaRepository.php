<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Repositories\AbstractRepository;
use App\Repositories\Manga\Concerns\HasMangaStatsSubQuery;
use App\Support\Manga\MangaNoteNormalizer;

use Framework\Support\Str;

final class MangaRepository extends AbstractRepository
{
    use HasMangaStatsSubQuery;

    protected string $table = 'manga';

    /** @return list<array{slug: string, livre: string, numero: int}> */
    public function releaseCollection(): array
    {
        $statement = $this->db->prepare("SELECT slug, livre, numero FROM {$this->readTable()}");
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return list<int> */
    public function ownedNumbers(string $slug): array
    {
        $statement = $this->db->prepare("SELECT numero FROM {$this->readTable()} WHERE slug = :slug AND {$this->ownerCondition()}");
        $statement->execute(['slug' => $this->normalizeSlug($slug)]);
        return array_map(static fn ($number): int => (int) $number, $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * @return list<Manga>
     */
    public function findBySlug(string $slug, int $perPage = 50, int $page = 1): array
    {
        $perPage = max(1, $perPage);
        $pageIndex = max(1, $page) - 1;
        $offset = $pageIndex > intdiv(PHP_INT_MAX, $perPage) ? PHP_INT_MAX : $pageIndex * $perPage;
        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT
                m.slug, m.numero, m.livre, m.thumbnail, m.extension, m.statut, m.note, m.lu,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->readTable('m')}

            INNER JOIN (
                {$this->statsSubQuery(true)}
            ) stats
                ON stats.slug = m.slug

            WHERE m.slug = :slug

            ORDER BY m.numero DESC, m.id DESC
            LIMIT {$perPage} OFFSET {$offset}
            ",
            ['slug' => $this->normalizeSlug($slug), 'stats_slug' => $this->normalizeSlug($slug)],
            Manga::class
        );

        return $mangas;
    }

    public function countBySlug(string $slug): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total FROM {$this->readTable()} WHERE slug = :slug AND {$this->ownerCondition()}",
            'total', ['slug' => $this->normalizeSlug($slug)]
        );
    }

    public function findRecordBySlugAndNumero(string $slug, int $numero): ?Manga
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->readTable()} WHERE slug = :slug AND {$this->ownerCondition()} AND numero = :numero LIMIT 1",
            ['slug' => $this->normalizeSlug($slug), 'numero' => $numero],
            Manga::class
        );
    }

    public function findOneBySlugAndNumero(string $slug, int $numero): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "
            SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->readTable('m')}

            INNER JOIN (
                {$this->statsSubQuery(true)}
            ) stats
                ON stats.slug = m.slug

            WHERE m.slug = :slug
            AND m.numero = :numero

            LIMIT 1
            ",
            ['slug' => $this->normalizeSlug($slug), 'stats_slug' => $this->normalizeSlug($slug), 'numero' => $numero],
            Manga::class
        );

        return $manga;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        return parent::insert($this->normalizeInsertData($data));
    }

    public function updateManga(
        string $slug,
        int $numero,
        ?string $editeur,
        string $statut,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire
    ): bool
    {
        [$jacquette, $livreNote] = $this->normalizeNotes($jacquette, $livreNote);

        $target = $this->fetchOne(
            "SELECT id FROM {$this->readTable()} WHERE slug = :slug AND {$this->ownerCondition()} AND numero = :numero LIMIT 1",
            ['slug' => $this->normalizeSlug($slug), 'numero' => $numero]
        );

        if ($target === null)
        {
            throw new \Framework\Http\Exceptions\NotFoundException('Manga introuvable');
        }

        $updated = $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'editeur' => Str::nullableTrim($editeur),
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $this->calculateNote($jacquette, $livreNote),
                'commentaire' => Str::nullableTrim($commentaire)
            ]
        );

        return $updated && $this->update(['statut' => trim($statut)], ['slug' => $this->normalizeSlug($slug)]);
    }

    public function updateReadStatus(string $slug, int $numero, bool $readStatus): bool
    {
        return $this->updateBySlugAndNumero($slug, $numero, ['lu' => (int) $readStatus]);
    }

    public function updateNote(string $slug, int $numero, ?int $jacquette, ?int $livreNote): \App\DTO\Manga\Responses\MangaUpdateNoteData|false
    {
        if (! $this->db->inTransaction())
        {
            throw new \LogicException('Note updates require a transaction.');
        }
        $target = $this->fetchOne(
            "SELECT id FROM {$this->table()} WHERE slug = :slug AND {$this->ownerCondition()} AND numero = :numero LIMIT 1 FOR UPDATE",
            ['slug' => $this->normalizeSlug($slug), 'numero' => $numero]
        );
        if ($target === null)
        {
            throw new \Framework\Http\Exceptions\NotFoundException('Manga introuvable');
        }

        [$jacquette, $livreNote] = $this->normalizeNotes($jacquette, $livreNote);

        $updated = $this->update(
            [
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $this->calculateNote($jacquette, $livreNote)
            ],
            ['id' => (int) $target->id]
        );

        return $updated ? new \App\DTO\Manga\Responses\MangaUpdateNoteData(
            jacquette: $jacquette ?? 0,
            livreNote: $livreNote ?? 0,
            note: ($jacquette ?? 0) + ($livreNote ?? 0)
        ) : false;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteExistingById($id);
    }

    public function seriesExists(string $slug): bool
    {
        $result = $this->fetchOne(
            "
            SELECT 1

            FROM {$this->readTable()}

            WHERE slug = :slug AND {$this->ownerCondition()}

            LIMIT 1
            ",
            ['slug' => $this->normalizeSlug($slug)]
        );

        return $result !== null;
    }

    public function claimReadReward(int $id): bool
    {
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET xp_read_rewarded = 1

            WHERE id = :id AND {$this->ownerCondition()}
            AND xp_read_rewarded = 0
            ",
            ['id' => $id]
        );

        return $statement !== false && $statement->rowCount() === 1;
    }

    public function lockSeries(string $slug): void
    {
        if (! $this->db->inTransaction())
        {
            throw new \LogicException('Series locks require a transaction.');
        }

        $statement = $this->query(
            "SELECT id FROM {$this->table()} WHERE slug = :slug AND {$this->ownerCondition()} ORDER BY id FOR UPDATE",
            ['slug' => $this->normalizeSlug($slug)]
        );
        if ($statement === false)
        {
            throw new \RuntimeException('Impossible de verrouiller la série.');
        }
        $statement->closeCursor();
    }

    public function claimSeriesReward(string $slug): bool
    {
        if (! $this->db->inTransaction())
        {
            throw new \LogicException('Series rewards must be claimed inside a transaction.');
        }

        $mangas = $this->fetchAll(
            "
            SELECT id, numero, lu, statut, xp_series_rewarded

            FROM {$this->table()}

            WHERE slug = :slug AND {$this->ownerCondition()}

            ORDER BY id

            FOR UPDATE
            ",
            ['slug' => $this->normalizeSlug($slug)],
            Manga::class
        );
        if ($mangas === [])
        {
            return false;
        }

        $alreadyRewarded = false;
        $fullyRead = true;
        $finished = false;

        foreach ($mangas as $manga)
        {
            $alreadyRewarded = $alreadyRewarded || $manga->xp_series_rewarded;
            $fullyRead = $fullyRead && $manga->lu;
            $finished = $finished || ($manga->numero === 1 && $manga->statut === 'termine');
        }

        if (! $fullyRead || ! $finished)
        {
            return false;
        }

        // Le journal survit a la suppression/recreation de tous les tomes.
        $history = $this->query(
            'INSERT INTO manga_series_rewards (user_id, slug) VALUES (:user_id, :slug)
            ON DUPLICATE KEY UPDATE slug = manga_series_rewards.slug',
            ['user_id' => $this->userId(), 'slug' => $this->normalizeSlug($slug)]
        );
        if ($history === false)
        {
            throw new \RuntimeException('Impossible de conserver la recompense de serie.');
        }
        $newReward = $history->rowCount() === 1;

        // Transmettre la récompense existante aux nouveaux tomes sans attribuer à nouveau des XP.
        $statement = $this->query(
            "
            UPDATE {$this->table()}

            SET xp_series_rewarded = 1

            WHERE slug = :slug AND {$this->ownerCondition()}
            AND xp_series_rewarded = 0
            ",
            ['slug' => $this->normalizeSlug($slug)]
        );

        return ! $alreadyRewarded && $newReward && $statement !== false && $statement->rowCount() >= 1;
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function normalizeSlug(string $slug): string
    {
        return Str::slug($slug);
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function normalizeNotes(?int $jacquette, ?int $livreNote): array
    {
        $jacquette = MangaNoteNormalizer::normalize($jacquette);
        $livreNote = MangaNoteNormalizer::normalize($livreNote);

        return [$jacquette, $livreNote];
    }

    private function calculateNote(?int $jacquette, ?int $livreNote): ?int
    {
        if ($jacquette === null || $livreNote === null)
        {
            return null;
        }

        return $jacquette + $livreNote;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateBySlugAndNumero(string $slug, int $numero, array $data): bool
    {
        return $this->update($data, ['slug' => $this->normalizeSlug($slug), 'numero' => $numero]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizeInsertData(array $data): array
    {
        [$jacquette, $livreNote] = $this->normalizeNotes($data['jacquette'] ?? null, $data['livre_note'] ?? null);

        return [
            'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
            'extension' => strtolower(trim((string) ($data['extension'] ?? ''))),
            'slug' => $this->normalizeSlug((string) ($data['slug'] ?? '')),
            'livre' => trim((string) ($data['livre'] ?? '')),
            'editeur' => Str::nullableTrim($data['editeur'] ?? null),
            'numero' => max(1, (int) ($data['numero'] ?? 1)),
            'lu' => 0,
            'statut' => trim((string) ($data['statut'] ?? 'en_cours')),
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
            'note' => $this->calculateNote($jacquette, $livreNote),
            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null)
        ];
    }
}
