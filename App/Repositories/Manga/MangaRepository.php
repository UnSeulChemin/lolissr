<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Core\Support\MangaNoteNormalizer;
use App\Core\Application\App;
use App\Core\Support\Str;
use App\Models\Manga;
use App\Models\Model;
use LogicException;

final class MangaRepository extends Model
{
    protected string $table = 'manga';

    private function guardWrite(): void
    {
        if (App::isReadOnly()) {
            throw new LogicException(
                'Écriture en base interdite en mode test.'
            );
        }
    }

    private function calculateNote(
        ?int $jacquette,
        ?int $livreNote
    ): ?int {
        if (
            $jacquette === null
            || $livreNote === null
        ) {
            return null;
        }

        return $jacquette + $livreNote;
    }

    private function fetchSingleValue(
        string $sql,
        string $field,
        array $params = [],
        mixed $default = 0
    ): mixed {
        $result = $this->fetchOne(
            $sql,
            $params
        );

        return $result->{$field}
            ?? $default;
    }

    /**
     * @return list<Manga>
     */
    public function searchMangas(
        string $search
    ): array {
        $search = trim(
            preg_replace(
                '/\s+/',
                ' ',
                trim($search)
            ) ?? ''
        );

        if ($search === '') {
            return [];
        }

        $pattern = '/^(.*?)(?:\s+(?:t|tome|vol|vol\.|volume|n°|no|#)?\s*0*([1-9][0-9]*))$/iu';

        if (
            preg_match(
                $pattern,
                $search,
                $matches
            )
        ) {
            $titlePart = trim($matches[1]);

            $numero = (int) $matches[2];

            if ($titlePart !== '') {
                return $this->fetchAll(
                    "SELECT *
                    FROM {$this->getTable()}
                    WHERE (
                        livre LIKE :search_livre
                        OR slug LIKE :search_slug
                    )
                    AND numero = :numero
                    ORDER BY livre ASC, numero ASC",
                    [
                        'search_livre' =>
                            "%{$titlePart}%",

                        'search_slug' =>
                            '%'
                            . Str::slug($titlePart)
                            . '%',

                        'numero' => $numero,
                    ],
                    Manga::class
                );
            }
        }

        return $this->fetchAll(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre LIKE :search_livre
            OR slug LIKE :search_slug
            ORDER BY livre ASC, numero ASC",
            [
                'search_livre' =>
                    "%{$search}%",

                'search_slug' =>
                    '%'
                    . Str::slug($search)
                    . '%',
            ],
            Manga::class
        );
    }

    public function countAllTomes(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}",
            'total'
        );
    }

    public function countSeries(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(DISTINCT livre) AS total
            FROM {$this->getTable()}",
            'total'
        );
    }

    public function countRead(): int
    {
        return (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE lu = 1",
            'total'
        );
    }

    public function averageNote(): ?float
    {
        $average = $this->fetchSingleValue(
            "SELECT AVG(COALESCE(note, 0)) AS moyenne
            FROM {$this->getTable()}",
            'moyenne',
            [],
            null
        );

        return $average !== null
            ? (float) $average
            : null;
    }

    public function findLastAdded(): ?Manga
    {
        return $this->fetchOne(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY id DESC
            LIMIT 1",
            [],
            Manga::class
        );
    }

    public function findLongestSeries(): ?object
    {
        return $this->fetchOne(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT 1
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            LIMIT 1"
        );
    }

    /**
     * @return array<int, object>
     */
    public function topLongestSeries(
        int $limit = 5
    ): array {
        $limit = max(1, $limit);

        return $this->fetchAll(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT {$limit}
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            ORDER BY counts.total DESC,
                     m1.livre ASC"
        );
    }

    /**
     * @return list<Manga>
     */
    public function findLowRatedMangas(
        int $limit = 5
    ): array {
        $limit = max(1, $limit);

        return $this->fetchAll(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY COALESCE(note, 0) ASC,
                     livre ASC,
                     numero ASC
            LIMIT {$limit}",
            [],
            Manga::class
        );
    }

    public function countFirstTomesPaginate(
        int $eachPerPage
    ): int {
        $eachPerPage = max(1, $eachPerPage);

        $total = (int) $this->fetchSingleValue(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE numero = 1",
            'total'
        );

        return max(
            1,
            (int) ceil($total / $eachPerPage)
        );
    }

    /**
     * @return list<Manga>
     */
    public function findAllFirstTomes(
        string $orderBy,
        int $eachPerPage,
        int $page
    ): array {
        $page = max(1, $page);

        $eachPerPage = max(1, $eachPerPage);

        $start =
            ($page - 1)
            * $eachPerPage;

        $allowedOrderBy = [
            'id DESC',
            'id ASC',
        ];

        if (
            !in_array(
                $orderBy,
                $allowedOrderBy,
                true
            )
        ) {
            $orderBy = 'id DESC';
        }

        return $this->fetchAll(
            "SELECT m.*,
                    stats.total,
                    stats.total_lu,
                    stats.average_note
            FROM {$this->getTable()} m
            INNER JOIN (
                SELECT slug,
                       COUNT(*) AS total,
                       SUM(
                           CASE
                               WHEN lu = 1 THEN 1
                               ELSE 0
                           END
                       ) AS total_lu,
                       ROUND(
                           AVG(COALESCE(note, 0)),
                           1
                       ) AS average_note
                FROM {$this->getTable()}
                GROUP BY slug
            ) stats ON stats.slug = m.slug
            WHERE m.numero = 1
            ORDER BY
                CASE
                    WHEN stats.total_lu < stats.total
                    THEN 0
                    ELSE 1
                END ASC,

                CASE
                    WHEN m.statut = 'termine'
                    THEN 1
                    ELSE 0
                END ASC,

                stats.average_note ASC,
                {$orderBy}

            LIMIT {$start}, {$eachPerPage}",
            [],
            Manga::class
        );
    }

    /**
     * @return list<Manga>
     */
    public function findBySlug(
        string $slug
    ): array {
        return $this->fetchAll(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => Str::slug($slug),
            ],
            Manga::class
        );
    }

    public function findOneBySlugAndNumero(
        string $slug,
        int $numero
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
            Manga::class
        );
    }

    public function insert(
        array $datas
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize(
            $datas['jacquette'] ?? null
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $datas['livre_note'] ?? null
        );

        return parent::insert([
            'thumbnail' => trim(
                (string) ($datas['thumbnail'] ?? '')
            ),

            'extension' => strtolower(
                trim(
                    (string) ($datas['extension'] ?? '')
                )
            ),

            'slug' => Str::slug(
                (string) ($datas['slug'] ?? '')
            ),

            'livre' => trim(
                (string) ($datas['livre'] ?? '')
            ),

            'editeur' => Str::nullableTrim(
                $datas['editeur'] ?? null
            ),

            'numero' => max(
                1,
                (int) ($datas['numero'] ?? 1)
            ),

            'lu' => 0,

            'statut' => trim(
                (string) (
                    $datas['statut']
                    ?? 'en_cours'
                )
            ),

            'jacquette' => $jacquette,

            'livre_note' => $livreNote,

            'note' => $this->calculateNote(
                $jacquette,
                $livreNote
            ),

            'commentaire' => Str::nullableTrim(
                $datas['commentaire'] ?? null
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
        ?string $commentaire
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize(
            $jacquette
        );

        $livreNote = MangaNoteNormalizer::normalize(
            $livreNote
        );

        return $this->update(
            [
                'editeur' => Str::nullableTrim(
                    $editeur
                ),

                'statut' => trim(
                    $statut
                ),

                'jacquette' => $jacquette,

                'livre_note' => $livreNote,

                'note' => $this->calculateNote(
                    $jacquette,
                    $livreNote
                ),

                'commentaire' => Str::nullableTrim(
                    $commentaire
                ),
            ],
            [
                'slug' => Str::slug(
                    $slug
                ),

                'numero' => $numero,
            ]
        );
    }

    public function updateLu(
        string $slug,
        int $numero,
        bool $lu
    ): bool {
        $this->guardWrite();

        return $this->update(
            [
                'lu' => $lu ? 1 : 0,
            ],
            [
                'slug' =>
                    Str::slug($slug),

                'numero' =>
                    $numero,
            ]
        );
    }

    public function updateNote(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote
    ): bool {
        $this->guardWrite();

        return $this->update(
            [
                'jacquette' =>
                    $jacquette,

                'livre_note' =>
                    $livreNote,

                'note' =>
                    $this->calculateNote(
                        $jacquette,
                        $livreNote
                    ),
            ],
            [
                'slug' =>
                    Str::slug($slug),

                'numero' =>
                    $numero,
            ]
        );
    }

    public function deleteBySlugAndNumero(
        string $slug,
        int $numero
    ): bool {
        $this->guardWrite();

        return $this->delete([
            'slug' =>
                Str::slug($slug),

            'numero' =>
                $numero,
        ]);
    }
}