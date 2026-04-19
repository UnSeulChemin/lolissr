<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Support\Str;
use App\Models\Model;

final class MangaRepository extends Model
{
    protected string $table = 'manga';

    /**
     * Convertit une note en int ou null.
     */
    private function normalizeNoteValue(mixed $value): ?int
    {
        if ($value === null || $value === '')
        {
            return null;
        }

        $value = (int) $value;

        if ($value < 1 || $value > 5)
        {
            return null;
        }

        return $value;
    }

    /**
     * Calcule la note finale sur 10.
     */
    private function calculateNote(?int $jacquette, ?int $livreNote): ?int
    {
        if ($jacquette === null || $livreNote === null)
        {
            return null;
        }

        return $jacquette + $livreNote;
    }

    /**
     * Recherche mangas par titre, slug
     * et numéro de tome (tome, t, vol, volume, n°, no, #, etc.)
     *
     * @return array<int, object>
     */
    public function searchMangas(string $search): array
    {
        $search = trim($search);

        if ($search === '')
        {
            return [];
        }

        $search = preg_replace('/\s+/', ' ', $search);
        $search = trim((string) $search);

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
                        livre COLLATE utf8mb4_unicode_ci LIKE :search_livre
                        OR slug COLLATE utf8mb4_unicode_ci LIKE :search_slug
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

        $search = preg_replace('/\s+(t|tome|vol|vol\.|volume|n°|no|#)$/iu', '', $search);
        $search = trim((string) $search);

        if ($search === '')
        {
            return [];
        }

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre COLLATE utf8mb4_unicode_ci LIKE :search_livre
            OR slug COLLATE utf8mb4_unicode_ci LIKE :search_slug
            ORDER BY livre ASC, numero ASC",
            [
                'search_livre' => '%' . $search . '%',
                'search_slug' => '%' . Str::slug($search) . '%'
            ]
        );

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Compte le nombre total de tomes.
     */
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

    /**
     * Compte le nombre total de séries.
     */
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

    /**
     * Récupère le dernier tome ajouté.
     */
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

    /**
     * Récupère la série la plus longue.
     */
    public function findLongestSeries(): object|false
    {
        $query = $this->requete(
            "SELECT m1.slug, m1.livre, m1.thumbnail, m1.extension, counts.total
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

    /**
     * Récupère la moyenne des notes.
     */
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

        if (!isset($result->moyenne))
        {
            return null;
        }

        return (float) $result->moyenne;
    }

    /**
     * Retourne le top des séries les plus longues.
     *
     * @return array<int, object>
     */
    public function topLongestSeries(int $limit = 5): array
    {
        $limit = max(1, (int) $limit);

        $query = $this->requete(
            "SELECT m1.slug, m1.livre, m1.thumbnail, m1.extension, counts.total
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

    /**
     * Récupère les mangas faiblement notés.
     *
     * @return array<int, object>
     */
    public function findLowRatedMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note IS NOT NULL
            AND note < 8
            ORDER BY note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Récupère les mangas avec jacquette faible.
     *
     * @return array<int, object>
     */
    public function findLowJacquetteMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE jacquette IS NOT NULL
            AND jacquette < 4
            ORDER BY jacquette ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Récupère les mangas avec état livre faible.
     *
     * @return array<int, object>
     */
    public function findLowLivreStateMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre_note IS NOT NULL
            AND livre_note < 4
            ORDER BY livre_note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        );

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Compte le nombre total de pages pour la collection des tomes 1.
     */
    public function countFirstTomesPaginate(int $eachPerPage): int
    {
        $eachPerPage = max(1, $eachPerPage);

        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}
            WHERE numero = ?",
            [1]
        );

        if ($query === false)
        {
            return 1;
        }

        $result = $query->fetch();
        $total = (int) ($result->total ?? 0);

        return max(1, (int) ceil($total / $eachPerPage));
    }

    /**
     * Récupère les tomes 1 paginés.
     *
     * @return array<int, object>
     */
    public function findAllFirstTomes(string $orderBy, int $eachPerPage, int $page): array
    {
        $page = max(1, $page);
        $eachPerPage = max(1, $eachPerPage);
        $start = ($page - 1) * $eachPerPage;

        $allowedOrderBy = ['id DESC', 'id ASC'];

        if (!in_array($orderBy, $allowedOrderBy, true))
        {
            $orderBy = 'id DESC';
        }

        $sql = "SELECT m.*,
                    (
                        SELECT COUNT(*)
                        FROM {$this->getTable()}
                        WHERE slug = m.slug
                    ) AS total
                FROM {$this->getTable()} m
                WHERE m.numero = :numero
                ORDER BY {$orderBy}
                LIMIT {$start}, {$eachPerPage}";

        $query = $this->requete(
            $sql,
            [
                'numero' => 1
            ]
        );

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Récupère tous les tomes d'un manga à partir de son slug.
     *
     * @return array<int, object>
     */
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

    /**
     * Récupère un tome précis via son slug et son numéro.
     */
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

    /**
     * Insère un manga en base.
     */
    public function insert(array $datas): bool
    {
        $jacquette = $this->normalizeNoteValue($datas['jacquette'] ?? null);
        $livreNote = $this->normalizeNoteValue($datas['livre_note'] ?? null);
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
                'numero' => max(0, (int) ($datas['numero'] ?? 0)),
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note,
                'commentaire' => $commentaire
            ]
        ) !== false;
    }

    /**
     * Met à jour un manga.
     */
    public function updateManga(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire
    ): bool
    {
        $slug = Str::slug($slug);
        $numero = (int) $numero;

        $jacquette = $this->normalizeNoteValue($jacquette);
        $livreNote = $this->normalizeNoteValue($livreNote);
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
                'slug' => $slug,
                'numero' => $numero
            ]
        );
    }
}