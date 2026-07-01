<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\MangaData;
use App\Models\Manga;
use App\Models\Model;
use App\Repositories\Manga\Concerns\HasMangaStatsSubQuery;

use Framework\Support\MangaNoteNormalizer;
use Framework\Support\Str;

final class MangaRepository extends Model
{
    use HasMangaStatsSubQuery;

    protected string $table = 'manga';

    /**
     * @return list<Manga>
     */
    public function findBySlug(string $slug): array
    {
        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT
                m.*,
                stats.total,
                stats.total_lu,
                stats.average_note

            FROM {$this->table()} m

            INNER JOIN (
                {$this->statsSubQuery()}
            ) stats
                ON stats.slug = m.slug

            WHERE m.slug = :slug

            ORDER BY m.numero DESC
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ],
            Manga::class
        );

        return $mangas;
    }

    /**
     * @return list<MangaData>
     */
    public function findBySlugDto(string $slug): array
    {
        return $this->mapResultsToDto($this->findBySlug($slug));
    }

    public function findOneBySlugAndNumero(string $slug, int $numero): ?Manga
    {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "
            SELECT *

            FROM {$this->table()}

            WHERE slug = :slug
            AND numero = :numero

            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
                'numero' => $numero,
            ],
            Manga::class
        );

        return $manga;
    }

    public function findOneDtoBySlugAndNumero(string $slug, int $numero): ?MangaData
    {
        $manga = $this->findOneBySlugAndNumero($slug, $numero);

        if ($manga === null)
        {
            return null;
        }

        return $this->mapToDto($manga);
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

        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'editeur' => Str::nullableTrim($editeur),
                'statut' => trim($statut),
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $this->calculateNote($jacquette, $livreNote),
                'commentaire' => Str::nullableTrim($commentaire),
            ]
        );
    }

    public function updateReadStatus(
        string $slug,
        int $numero,
        bool $readStatus
    ): bool
    {
        return $this->updateBySlugAndNumero($slug, $numero, ['lu' => (int) $readStatus]);
    }

    public function updateNote(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote
    ): bool
    {
        [$jacquette, $livreNote] = $this->normalizeNotes($jacquette, $livreNote);

        return $this->updateBySlugAndNumero(
            $slug,
            $numero,
            [
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $this->calculateNote($jacquette, $livreNote),
            ]
        );
    }

    public function deleteBySlugAndNumero(string $slug, int $numero): bool
    {
        return $this->delete(['slug' => $this->normalizeSlug($slug), 'numero' => $numero]);
    }

    public function seriesExists(string $slug): bool
    {
        $result = $this->fetchOne(
            "
            SELECT 1

            FROM {$this->table()}

            WHERE slug = :slug

            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ]
        );

        return $result !== null;
    }

    /**
     * @return list<Manga>
     */
    public function findReadWithoutReward(): array
    {
        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll(
            "
            SELECT *

            FROM {$this->table()}

            WHERE lu = 1
            AND xp_read_rewarded = 0
            ",
            [],
            Manga::class
        );

        return $mangas;
    }

    public function markXpRewarded(int $id): bool
    {
        return $this->update(['xp_read_rewarded' => 1], ['id' => $id]);
    }

    public function isSeriesRewarded(string $slug): bool
    {
        $result = $this->fetchOne(
            "
            SELECT xp_series_rewarded

            FROM {$this->table()}

            WHERE slug = :slug

            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ]
        );

        if ($result === null)
        {
            return true;
        }

        /** @var array{xp_series_rewarded?: mixed} $data */
        $data = (array) $result;

        return (bool) ($data['xp_series_rewarded'] ?? false);
    }

    public function markSeriesRewardedBySlug(string $slug): bool
    {
        return $this->execute(
            "
            UPDATE {$this->table()}

            SET xp_series_rewarded = 1

            WHERE slug = :slug
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapToDto(Manga $manga): MangaData
    {
        return new MangaData(
            id: $manga->id,
            slug: $manga->slug,
            livre: $manga->livre,

            thumbnail: $manga->thumbnail !== '' ? $manga->thumbnail : null,
            extension: $manga->extension !== '' ? $manga->extension : null,

            editeur: $manga->editeur,

            numero: $manga->numero,
            lu: $manga->lu,

            statut: $manga->statut,

            jacquette: $manga->jacquette,
            livreNote: $manga->livre_note,
            note: $manga->note,

            commentaire: $manga->commentaire,

            total: $manga->total,
            totalLu: $manga->total_lu,
            averageNote: $manga->average_note,

            xpReadRewarded: $manga->xp_read_rewarded,
            xpSeriesRewarded: $manga->xp_series_rewarded
        );
    }

    /**
     * @param list<Manga> $mangas
     * @return list<MangaData>
     */
    private function mapResultsToDto(array $mangas): array
    {
        return array_map($this->mapToDto(...), $mangas);
    }

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
            'commentaire' => Str::nullableTrim($data['commentaire'] ?? null),
        ];
    }
}
