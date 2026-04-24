<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Core\Application\App;
use App\Core\Support\Str;
use App\DTO\Manga\MangaNoteNormalizer;
use App\Models\Model;
use LogicException;

final class MangaRepository extends Model
{
    protected string $table = 'manga';

    private function guardWrite(): void
    {
        if (App::isReadOnly())
        {
            throw new LogicException('Écriture en base interdite en mode test.');
        }
    }

    private function calculateNote(?int $jacquette, ?int $livreNote): ?int
    {
        if ($jacquette === null || $livreNote === null)
        {
            return null;
        }

        return $jacquette + $livreNote;
    }

    public function searchMangas(string $search): array
    {
        $search = trim($search);

        if ($search === '')
        {
            return [];
        }

        $search = trim(preg_replace('/\s+/', ' ', $search) ?? '');

        $pattern = '/^(.*?)(?:\s+(?:t|tome|vol|vol\.|volume|n°|no|#)?\s*0*([1-9][0-9]*))$/iu';

        if (preg_match($pattern, $search, $matches))
        {
            $titlePart = trim($matches[1]);
            $numero = (int) $matches[2];

            if ($titlePart !== '')
            {
                $query = $this->requete(
                    "SELECT *
                    FROM {$this->getTable()}
                    WHERE (
                        livre LIKE :search_livre
                        OR slug LIKE :search_slug
                    )
                    AND numero = :numero
                    ORDER BY livre ASC, numero ASC",
                    [
                        'search_livre' => '%' . $titlePart . '%',
                        'search_slug' => '%' . Str::slug($titlePart) . '%',
                        'numero' => $numero
                    ]
                );

                return $query ? $query->fetchAll() : [];
            }
        }

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre LIKE :search_livre
            OR slug LIKE :search_slug
            ORDER BY livre ASC, numero ASC",
            [
                'search_livre' => '%' . $search . '%',
                'search_slug' => '%' . Str::slug($search) . '%'
            ]
        );

        return $query ? $query->fetchAll() : [];
    }

    public function countAllTomes(): int
    {
        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}"
        );

        if ($query === false)
        {
            return 0;
        }

        $result = $query->fetch();

        return (int) ($result->total ?? 0);
    }

    public function countSeries(): int
    {
        $query = $this->requete(
            "SELECT COUNT(DISTINCT livre) AS total
            FROM {$this->getTable()}"
        );

        if ($query === false)
        {
            return 0;
        }

        $result = $query->fetch();

        return (int) ($result->total ?? 0);
    }

    public function findLastAdded(): object|false
    {
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY id DESC
            LIMIT 1"
        );

        return $query ? $query->fetch() : false;
    }

    public function averageNote(): ?float
    {
        $query = $this->requete(
            "SELECT AVG(note) AS moyenne
            FROM {$this->getTable()}
            WHERE note IS NOT NULL"
        );

        if ($query === false)
        {
            return null;
        }

        $result = $query->fetch();

        return isset($result->moyenne)
            ? (float) $result->moyenne
            : null;
    }

    public function findLongestSeries(): object|false
    {
        $query = $this->requete(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug, COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT 1
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            LIMIT 1"
        );

        return $query ? $query->fetch() : false;
    }

    public function topLongestSeries(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $query = $this->requete(
            "SELECT m1.slug,
                    m1.livre,
                    m1.thumbnail,
                    m1.extension,
                    counts.total
            FROM {$this->getTable()} m1
            INNER JOIN (
                SELECT slug, COUNT(*) AS total
                FROM {$this->getTable()}
                GROUP BY slug
                ORDER BY total DESC
                LIMIT {$limit}
            ) counts ON counts.slug = m1.slug
            WHERE m1.numero = 1
            ORDER BY counts.total DESC, m1.livre ASC"
        );

        return $query ? $query->fetchAll() : [];
    }

    public function findLowRatedMangas(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note IS NOT NULL
            ORDER BY note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    public function findLowJacquetteMangas(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE jacquette IS NOT NULL
            ORDER BY jacquette ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    public function findLowLivreStateMangas(int $limit = 5): array
    {
        $limit = max(1, $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre_note IS NOT NULL
            ORDER BY livre_note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    public function countFirstTomesPaginate(int $eachPerPage): int
    {
        $eachPerPage = max(1, $eachPerPage);

        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE numero = 1"
        );

        if ($query === false)
        {
            return 1;
        }

        $result = $query->fetch();
        $total = (int) ($result->total ?? 0);

        return max(1, (int) ceil($total / $eachPerPage));
    }

    public function findAllFirstTomes(
        string $orderBy,
        int $eachPerPage,
        int $page
    ): array {
        $page = max(1, $page);
        $eachPerPage = max(1, $eachPerPage);
        $start = ($page - 1) * $eachPerPage;

        $allowedOrderBy = ['id DESC', 'id ASC'];

        if (!in_array($orderBy, $allowedOrderBy, true))
        {
            $orderBy = 'id DESC';
        }

        $query = $this->requete(
            "SELECT m.*,
                (
                    SELECT COUNT(*)
                    FROM {$this->getTable()}
                    WHERE slug = m.slug
                ) AS total
            FROM {$this->getTable()} m
            WHERE m.numero = 1
            ORDER BY {$orderBy}
            LIMIT {$start}, {$eachPerPage}"
        );

        return $query ? $query->fetchAll() : [];
    }

    public function findBySlug(string $slug): array
    {
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => Str::slug($slug)
            ]
        );

        return $query ? $query->fetchAll() : [];
    }

    public function findOneBySlugAndNumero(string $slug, int $numero): object|false
    {
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            AND numero = :numero",
            [
                'slug' => Str::slug($slug),
                'numero' => $numero
            ]
        );

        return $query ? $query->fetch() : false;
    }

    public function insert(array $datas): bool
    {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize($datas['jacquette'] ?? null);
        $livreNote = MangaNoteNormalizer::normalize($datas['livre_note'] ?? null);
        $commentaire = Str::nullableTrim($datas['commentaire'] ?? null);
        $note = $this->calculateNote($jacquette, $livreNote);

        return $this->requete(
            "INSERT INTO {$this->getTable()} (
                thumbnail,
                extension,
                slug,
                livre,
                numero,
                jacquette,
                livre_note,
                note,
                commentaire,
                created_at
            )
            VALUES (
                :thumbnail,
                :extension,
                :slug,
                :livre,
                :numero,
                :jacquette,
                :livre_note,
                :note,
                :commentaire,
                NOW()
            )",
            [
                'thumbnail' => trim((string) ($datas['thumbnail'] ?? '')),
                'extension' => strtolower(trim((string) ($datas['extension'] ?? ''))),
                'slug' => Str::slug((string) ($datas['slug'] ?? '')),
                'livre' => trim((string) ($datas['livre'] ?? '')),
                'numero' => max(1, (int) ($datas['numero'] ?? 1)),
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note,
                'commentaire' => $commentaire
            ]
        ) !== false;
    }

    public function updateManga(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire
    ): bool {
        $this->guardWrite();

        $jacquette = MangaNoteNormalizer::normalize($jacquette);
        $livreNote = MangaNoteNormalizer::normalize($livreNote);
        $commentaire = Str::nullableTrim($commentaire);
        $note = $this->calculateNote($jacquette, $livreNote);

        return $this->update(
            [
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note,
                'commentaire' => $commentaire
            ],
            [
                'slug' => Str::slug($slug),
                'numero' => $numero
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

        $jacquette = MangaNoteNormalizer::normalize($jacquette);
        $livreNote = MangaNoteNormalizer::normalize($livreNote);
        $note = $this->calculateNote($jacquette, $livreNote);

        return $this->update(
            [
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note
            ],
            [
                'slug' => Str::slug($slug),
                'numero' => $numero
            ]
        );
    }

    public function deleteBySlugAndNumero(string $slug, int $numero): bool
    {
        $this->guardWrite();

        return $this->delete(
            [
                'slug' => Str::slug($slug),
                'numero' => $numero
            ]
        );
    }
}