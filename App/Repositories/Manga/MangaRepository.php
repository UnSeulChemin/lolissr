<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\DTO\Manga\Responses\MangaData;
use App\Models\Model;
use App\Models\Manga;

use Framework\Application\App;
use Framework\Support\MangaNoteNormalizer;
use Framework\Support\Str;

use LogicException;

final class MangaRepository extends Model
{
    protected string $table = 'manga';

    private function mapToDto(
        Manga $manga,
    ): MangaData
    {
        return new MangaData(
            id: $manga->id,
            slug: $manga->slug,
            livre: $manga->livre,

            thumbnail:
                $manga->thumbnail !== ''
                    ? $manga->thumbnail
                    : null,

            extension:
                $manga->extension !== ''
                    ? $manga->extension
                    : null,

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

            xpReadRewarded:
                (bool) $manga->xp_read_rewarded,

            xpSeriesRewarded:
                (bool) $manga->xp_series_rewarded,
        );
    }

    private function guardWrite(): void
    {
        if (! App::isReadOnly())
        {
            return;
        }

        throw new LogicException(
            'Écriture en base interdite en mode lecture seule.',
        );
    }

    private function normalizeSlug(
        string $slug,
    ): string {
        return Str::slug($slug);
    }

    private function calculateNote(
        ?int $jacquette,
        ?int $livreNote,
    ): ?int {
        if (
            $jacquette === null
            || $livreNote === null
        ) {
            return null;
        }

        return $jacquette + $livreNote;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeInsertData(
        array $data,
    ): array {
        $jacquette = MangaNoteNormalizer::normalize(
            $data['jacquette'] ?? null,
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $data['livre_note'] ?? null,
        );

        return [
            'thumbnail' => trim(
                (string) ($data['thumbnail'] ?? ''),
            ),

            'extension' => strtolower(
                trim(
                    (string) ($data['extension'] ?? ''),
                ),
            ),

            'slug' => $this->normalizeSlug(
                (string) ($data['slug'] ?? ''),
            ),

            'livre' => trim(
                (string) ($data['livre'] ?? ''),
            ),

            'editeur' => Str::nullableTrim(
                $data['editeur'] ?? null,
            ),

            'numero' => max(
                1,
                (int) ($data['numero'] ?? 1),
            ),

            'lu' => 0,

            'statut' => trim(
                (string) (
                    $data['statut']
                    ?? 'en_cours'
                ),
            ),

            'jacquette' => $jacquette,

            'livre_note' => $livreNote,

            'note' => $this->calculateNote(
                $jacquette,
                $livreNote,
            ),

            'commentaire' => Str::nullableTrim(
                $data['commentaire'] ?? null,
            ),
        ];
    }

    /**
     * @return list<Manga>
     */
    public function findBySlug(
        string $slug,
    ): array {

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
                'slug' => $this->normalizeSlug(
                    $slug,
                ),
            ],
            Manga::class,
        );

        return $mangas;
    }

    /**
     * @return list<MangaData>
     */
    public function findBySlugDto(
        string $slug,
    ): array {

        return array_map(
            fn (Manga $manga)
                => $this->mapToDto($manga),
            $this->findBySlug($slug),
        );
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero,
    ): ?Manga {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "SELECT *
            FROM {$this->table()}
            WHERE slug = :slug
            AND numero = :numero
            LIMIT 1",
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),

                'numero' => $numero,
            ],
            Manga::class,
        );

        return $manga;
    }

    public function findOneDtoBySlugAndNumero(
        string $slug,
        int $numero,
    ): ?MangaData {

        $manga =
            $this->findOneBySlugAndNumero(
                $slug,
                $numero,
            );

        if ($manga === null)
        {
            return null;
        }

        return $this->mapToDto($manga);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(
        array $data,
    ): bool {
        $this->guardWrite();

        return parent::insert(
            $this->normalizeInsertData(
                $data,
            ),
        );
    }

    public function updateManga(
        string $slug,
        int $numero,
        ?string $editeur,
        string $statut,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire,
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize(
            $jacquette,
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $livreNote,
        );

        return $this->update(
            [
                'editeur' => Str::nullableTrim(
                    $editeur,
                ),

                'statut' => trim(
                    $statut,
                ),

                'jacquette' => $jacquette,

                'livre_note' => $livreNote,

                'note' => $this->calculateNote(
                    $jacquette,
                    $livreNote,
                ),

                'commentaire' => Str::nullableTrim(
                    $commentaire,
                ),
            ],
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),

                'numero' => $numero,
            ],
        );
    }

    public function updateReadStatus(
        string $slug,
        int $numero,
        bool $readStatus,
    ): bool {
        $this->guardWrite();

        return $this->update(
            [
                'lu' => (int) $readStatus,
            ],
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),

                'numero' => $numero,
            ],
        );
    }

    public function updateNote(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote,
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize(
            $jacquette,
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $livreNote,
        );

        return $this->update(
            [
                'jacquette' => $jacquette,

                'livre_note' => $livreNote,

                'note' => $this->calculateNote(
                    $jacquette,
                    $livreNote,
                ),
            ],
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),

                'numero' => $numero,
            ],
        );
    }

    public function deleteBySlugAndNumero(
        string $slug,
        int $numero,
    ): bool {
        $this->guardWrite();

        return $this->delete(
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),

                'numero' => $numero,
            ],
        );
    }

    public function seriesExists(
        string $slug,
    ): bool {
        $result = $this->fetchOne(
            "SELECT 1
            FROM {$this->table()}
            WHERE slug = :slug
            LIMIT 1",
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),
            ],
        );

        return $result !== null;
    }

    private function statsSubQuery(): string
    {
        return "
            SELECT
                slug,
                COUNT(*) AS total,
                SUM(
                    CASE
                        WHEN lu = 1 THEN 1
                        ELSE 0
                    END
                ) AS total_lu,
                ROUND(
                    AVG(
                        COALESCE(note, 0)
                    ),
                    1
                ) AS average_note
            FROM {$this->table()}
            GROUP BY slug
        ";
    }

    /**
     * @return list<Manga>
     */
    public function findSeriesWithoutPerfectNote(): array
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

            WHERE m.numero = 1
            AND stats.average_note < 10

            ORDER BY
                stats.average_note ASC,
                m.livre ASC
            ",
            [],
            Manga::class,
        );

        return $mangas;
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
            Manga::class,
        );

        return $mangas;
    }

    public function markXpRewarded(
        int $id,
    ): bool {
        $this->guardWrite();

        return $this->update(
            [
                'xp_read_rewarded' => 1,
            ],
            [
                'id' => $id,
            ],
        );
    }

    public function isSeriesRewarded(
        string $slug,
    ): bool {

        $result = $this->fetchOne(
            "
            SELECT xp_series_rewarded
            FROM {$this->table()}
            WHERE slug = :slug
            LIMIT 1
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ],
        );

        if ($result === null)
        {
            return true;
        }

        return
            (bool) $result->xp_series_rewarded;
    }

    public function markSeriesRewardedBySlug(
        string $slug,
    ): bool {

        $this->guardWrite();

        return $this->execute(
            "
            UPDATE {$this->table()}
            SET xp_series_rewarded = 1
            WHERE slug = :slug
            ",
            [
                'slug' => $this->normalizeSlug($slug),
            ],
        );
    }
}
