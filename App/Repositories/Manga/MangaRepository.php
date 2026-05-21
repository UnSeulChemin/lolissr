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
        if (App::isReadOnly()) {
            throw new LogicException(
                'Écriture en base interdite en mode test.',
            );
        }
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
     * @return list<Manga>
     */
    public function findBySlug(
        string $slug,
    ): array {
        return $this->fetchAll(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => Str::slug($slug),
            ],
            Manga::class,
        );
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero,
    ): ?Manga {
        return $this->fetchOne(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            AND numero = :numero",
            [
                'slug' => Str::slug($slug),
                'numero' => $numero,
            ],
            Manga::class,
        );
    }

    /**
     * @param array<string, mixed> $datas
     */
    public function insert(
        array $datas,
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize(
            $datas['jacquette'] ?? null,
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $datas['livre_note'] ?? null,
        );

        return parent::insert([
            'thumbnail' => trim(
                (string) ($datas['thumbnail'] ?? ''),
            ),

            'extension' => strtolower(
                trim(
                    (string) ($datas['extension'] ?? ''),
                ),
            ),

            'slug' => Str::slug(
                (string) ($datas['slug'] ?? ''),
            ),

            'livre' => trim(
                (string) ($datas['livre'] ?? ''),
            ),

            'editeur' => Str::nullableTrim(
                $datas['editeur'] ?? null,
            ),

            'numero' => max(
                1,
                (int) ($datas['numero'] ?? 1),
            ),

            'lu' => 0,

            'statut' => trim(
                (string) (
                    $datas['statut']
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
                $datas['commentaire'] ?? null,
            ),
        ]);
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
                'slug' => Str::slug($slug),
                'numero' => $numero,
            ],
        );
    }

    public function updateLu(
        string $slug,
        int $numero,
        bool $lu,
    ): bool {
        $this->guardWrite();

        return $this->update(
            [
                'lu' => $lu ? 1 : 0,
            ],
            [
                'slug' => Str::slug($slug),
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
                'slug' => Str::slug($slug),
                'numero' => $numero,
            ],
        );
    }

    public function deleteBySlugAndNumero(
        string $slug,
        int $numero,
    ): bool {
        $this->guardWrite();

        return $this->delete([
            'slug' => Str::slug($slug),
            'numero' => $numero,
        ]);
    }
}
