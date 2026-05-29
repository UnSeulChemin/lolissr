<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Models\Model;
use Framework\Application\App;
use Framework\Support\MangaNoteNormalizer;
use Framework\Support\Str;
use LogicException;

final class MangaRepository extends Model
{
    protected string $table = 'manga';

    private function guardWrite(): void
    {
        if (!App::isReadOnly()) {
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
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => $this->normalizeSlug(
                    $slug,
                ),
            ],
            Manga::class,
        );

        return $mangas;
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero,
    ): ?Manga {
        /** @var Manga|null $manga */
        $manga = $this->fetchOne(
            "SELECT *
            FROM {$this->getTable()}
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
            FROM {$this->getTable()}
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
}
