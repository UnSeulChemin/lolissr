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
     * Calcule la note finale.
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
     * Récupère tous les mangas notés 10/10.
     */
    public function findBestRatedMangas(): array
    {
        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note = 10
            ORDER BY livre ASC, numero ASC"
        )->fetchAll();
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
     * Récupère tous les mangas ayant la meilleure note existante.
     */
    public function findAllBestRated(): array
    {
        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note IS NOT NULL
            AND note = (
                SELECT MAX(note)
                FROM {$this->getTable()}
                WHERE note IS NOT NULL
            )
            ORDER BY livre ASC, numero ASC"
        )->fetchAll();
    }

    /**
     * Récupère un seul manga avec la meilleure note.
     */
    public function findBestRated(): object|false
    {
        return $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE note IS NOT NULL
            ORDER BY note DESC, id DESC
            LIMIT 1"
        )->fetch();
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

        $query = $this->requete($sql, [
            'numero' => 1
        ]);

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