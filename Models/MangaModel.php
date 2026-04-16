<?php

namespace App\Models;

use App\Core\Functions;
use App\Models\Trait\CreatedAtTrait;

class MangaModel extends Model
{
    use CreatedAtTrait;

    protected string $table = 'manga';

    protected int $id;
    protected string $thumbnail;
    protected string $extension;
    protected string $slug;
    protected string $livre;
    protected int $numero;
    protected ?int $jacquette = null;
    protected ?int $livre_note = null;
    protected ?int $note = null;
    protected ?string $commentaire = null;

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
     * Recherche mangas par titre, slug
     * et numéro de tome (tome, t, vol, volume, n°, no, #, etc.)
     */
    public function searchMangas(string $search): array
    {
        $search = trim($search);

        if ($search === '')
        {
            return [];
        }

        /* Nettoyage espaces */
        $search = preg_replace('/\s+/', ' ', $search);
        $search = trim($search);

        /*
        |-------------------------------------------------------
        | Détecte un numéro de tome à la fin
        | Exemples :
        | - rave 1
        | - rave 01
        | - rave tome 1
        | - rave t1
        | - rave t01
        | - rave vol 1
        | - rave vol.1
        | - rave volume 1
        | - rave n°1
        | - rave no 1
        | - rave #1
        |-------------------------------------------------------
        */
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
                        'search_slug' => '%' . Functions::normalizeSlug($titlePart) . '%',
                        'numero' => $numero
                    ]
                );

                return $query ? $query->fetchAll() : [];
            }
        }

        /*
        |-------------------------------------------------------
        | Nettoie un suffixe tome sans numéro
        | Exemples :
        | - rave tome
        | - rave t
        | - rave vol
        | - rave vol.
        | - rave volume
        | - rave n°
        | - rave no
        | - rave #
        |-------------------------------------------------------
        */
        $search = preg_replace('/\s+(t|tome|vol|vol\.|volume|n°|no|#)$/iu', '', $search);
        $search = trim($search);

        if ($search === '')
        {
            return [];
        }

        /*
        |-------------------------------------------------------
        | Recherche simple (sans numéro)
        |-------------------------------------------------------
        */
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre COLLATE utf8mb4_unicode_ci LIKE :search_livre
            OR slug COLLATE utf8mb4_unicode_ci LIKE :search_slug
            ORDER BY livre ASC, numero ASC",
            [
                'search_livre' => '%' . $search . '%',
                'search_slug' => '%' . Functions::normalizeSlug($search) . '%'
            ]
        );

        return $query ? $query->fetchAll() : [];
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
     * Compte le nombre total de tomes.
     */
    public function countAllTomes(): int
    {
        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->getTable()}"
        );

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

        $result = $query->fetch();

        return (int) ($result->total ?? 0);
    }

    /**
     * Récupère le dernier tome ajouté.
     */
    public function findLastAdded(): object|false
    {
        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            ORDER BY id DESC
            LIMIT 1"
        )->fetch();
    }

    /**
     * Récupère la série la plus longue.
     */
    public function findLongestSeries(): object|false
    {
        return $this->requete(
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
        )->fetch();
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

        $result = $query->fetch();

        if (!isset($result->moyenne))
        {
            return null;
        }

        return (float) $result->moyenne;
    }

    /**
     * Retourne le top des séries les plus longues.
     */
    public function topLongestSeries(int $limit = 5): array
    {
        $limit = max(1, (int) $limit);

        return $this->requete(
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
        )->fetchAll();
    }

    /**
     * Récupère les mangas faiblement notés.
     * Règle : note strictement inférieure à 8/10.
     */
    public function findLowRatedMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note IS NOT NULL
            AND note < 8
            ORDER BY note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        )->fetchAll();
    }

    /**
     * Récupère les mangas avec jacquette faible.
     * Règle : jacquette strictement inférieure à 4/5.
     */
    public function findLowJacquetteMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE jacquette IS NOT NULL
            AND jacquette < 4
            ORDER BY jacquette ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        )->fetchAll();
    }

    /**
     * Récupère les mangas avec état livre faible.
     * Règle : livre_note strictement inférieure à 4/5.
     */
    public function findLowLivreStateMangas(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE livre_note IS NOT NULL
            AND livre_note < 4
            ORDER BY livre_note ASC, livre ASC, numero ASC
            LIMIT {$limit}"
        )->fetchAll();
    }

    /**
     * Compte le nombre total de pages
     * pour la collection générale des tomes 1.
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

        $result = $query->fetch();
        $total = (int) ($result->total ?? 0);

        return max(1, (int) ceil($total / $eachPerPage));
    }

    /**
     * Récupère les tomes 1 paginés
     * avec le total de tomes par série.
     */
    public function findAllFirstTomes(string $orderBy, int $eachPerPage, int $page): array
    {
        $page = max(1, $page);
        $eachPerPage = max(1, $eachPerPage);
        $start = ($page - 1) * $eachPerPage;

        $orderByAutorises = ['id DESC', 'id ASC'];

        if (!in_array($orderBy, $orderByAutorises, true))
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
     */
    public function findBySlug(string $slug): array
    {
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => Functions::normalizeSlug($slug)
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
                'slug' => Functions::normalizeSlug($slug),
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
        $commentaire = Functions::normalizeCommentaire($datas['commentaire'] ?? null);
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
                'thumbnail' => trim($datas['thumbnail'] ?? ''),
                'extension' => strtolower(trim($datas['extension'] ?? '')),
                'slug' => Functions::normalizeSlug($datas['slug'] ?? ''),
                'livre' => trim($datas['livre'] ?? ''),
                'numero' => max(0, (int) ($datas['numero'] ?? 0)),
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note,
                'commentaire' => $commentaire
            ]
        ) !== false;
    }

    /**
     * Met à jour un manga :
     * jacquette, livre_note, note et commentaire.
     */
    public function updateManga(
        string $slug,
        int $numero,
        ?int $jacquette,
        ?int $livreNote,
        ?string $commentaire
    ): bool
    {
        $slug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        $jacquette = $this->normalizeNoteValue($jacquette);
        $livreNote = $this->normalizeNoteValue($livreNote);
        $commentaire = Functions::normalizeCommentaire($commentaire);
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

    /**
     * Retourne l'id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Définit l'id.
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Retourne le thumbnail.
     */
    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    /**
     * Définit le thumbnail.
     */
    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = trim($thumbnail);
        return $this;
    }

    /**
     * Retourne l'extension.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Définit l'extension.
     */
    public function setExtension(string $extension): self
    {
        $this->extension = strtolower(trim($extension));
        return $this;
    }

    /**
     * Retourne le slug.
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Définit le slug.
     */
    public function setSlug(string $slug): self
    {
        $this->slug = Functions::normalizeSlug($slug);
        return $this;
    }

    /**
     * Retourne le livre.
     */
    public function getLivre(): string
    {
        return $this->livre;
    }

    /**
     * Définit le livre.
     */
    public function setLivre(string $livre): self
    {
        $this->livre = trim($livre);
        return $this;
    }

    /**
     * Retourne le numéro.
     */
    public function getNumero(): int
    {
        return $this->numero;
    }

    /**
     * Définit le numéro.
     */
    public function setNumero(int $numero): self
    {
        $this->numero = max(0, $numero);
        return $this;
    }

    /**
     * Retourne la note jacquette.
     */
    public function getJacquette(): ?int
    {
        return $this->jacquette;
    }

    /**
     * Définit la note jacquette.
     */
    public function setJacquette(?int $jacquette): self
    {
        $this->jacquette = $this->normalizeNoteValue($jacquette);
        return $this;
    }

    /**
     * Retourne la note du livre.
     */
    public function getLivreNote(): ?int
    {
        return $this->livre_note;
    }

    /**
     * Définit la note du livre.
     */
    public function setLivreNote(?int $livre_note): self
    {
        $this->livre_note = $this->normalizeNoteValue($livre_note);
        return $this;
    }

    /**
     * Retourne la note totale.
     */
    public function getNote(): ?int
    {
        return $this->note;
    }

    /**
     * Définit la note totale.
     */
    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Retourne le commentaire.
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * Définit le commentaire.
     */
    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = Functions::normalizeCommentaire($commentaire);
        return $this;
    }
}