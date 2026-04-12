<?php

namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class MangaModel extends Model
{
    use CreatedAtTrait;

    protected string $table = 'manga';

    protected int $id;
    protected string $thumbnail;
    protected string $extension;
    protected string $slug;
    protected int $numero;
    protected ?int $jacquette = null;
    protected ?int $livre_note = null;
    protected ?int $note = null;
    protected string $livre;

    /**
     * nb total de pages pour les tomes 1
     */
    public function countFirstTomesPaginate(int $eachPerPage): int
    {
        $eachPerPage = max(1, $eachPerPage);

        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE numero = ?",
            [1]
        );

        $result = $query->fetch();

        return (int) ceil((($result->total) ?? 0) / $eachPerPage);
    }

    /**
     * récup tous les tomes 1 paginés
     * + total de tomes par série
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
                        FROM {$this->table}
                        WHERE slug = m.slug
                    ) AS total
                FROM {$this->table} m
                WHERE m.numero = :numero
                ORDER BY {$orderBy}
                LIMIT {$start}, {$eachPerPage}";

        return $this->requete($sql, [
            'numero' => 1
        ])->fetchAll();
    }

    /**
     * récup tous les tomes d'un slug
     */
    public function findBySlug(string $slug): array
    {
        return $this->requete(
            "SELECT *
            FROM {$this->table}
            WHERE slug = :slug
            ORDER BY numero DESC",
            [
                'slug' => strtolower(trim($slug))
            ]
        )->fetchAll();
    }

    /**
     * récup un manga précis via slug + numero
     */
    public function findOneBySlugAndNumero(string $slug, int $numero): object|false
    {
        return $this->requete(
            "SELECT *
            FROM {$this->table}
            WHERE slug = :slug
            AND numero = :numero",
            [
                'slug' => strtolower(trim($slug)),
                'numero' => $numero
            ]
        )->fetch();
    }

    /**
     * insert manga
     */
    public function insert(array $datas): bool
    {
        $jacquette = isset($datas['jacquette']) ? (is_null($datas['jacquette']) ? null : (int) $datas['jacquette']) : null;
        $livreNote = isset($datas['livre_note']) ? (is_null($datas['livre_note']) ? null : (int) $datas['livre_note']) : null;
        $note = null;

        if ($jacquette !== null && $livreNote !== null)
        {
            $note = $jacquette + $livreNote;
        }

        return $this->requete(
            "INSERT INTO {$this->table} (
                thumbnail,
                extension,
                slug,
                livre,
                numero,
                jacquette,
                livre_note,
                note,
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
                NOW()
            )",
            [
                'thumbnail' => trim($datas['thumbnail']),
                'extension' => strtolower(trim($datas['extension'])),
                'slug' => strtolower(trim($datas['slug'])),
                'livre' => trim($datas['livre']),
                'numero' => (int) $datas['numero'],
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
                'note' => $note
            ]
        ) !== false;
    }

    /**
     * update jacquette + livre_note + note auto
     */
    public function updateNotes(string $slug, int $numero, ?int $jacquette, ?int $livre_note): bool
    {
        $slug = strtolower(trim($slug));
        $numero = (int) $numero;
        $note = null;

        if ($jacquette !== null)
        {
            $jacquette = (int) $jacquette;
        }

        if ($livre_note !== null)
        {
            $livre_note = (int) $livre_note;
        }

        if ($jacquette !== null && $livre_note !== null)
        {
            $note = $jacquette + $livre_note;
        }

        return $this->update(
            [
                'jacquette' => $jacquette,
                'livre_note' => $livre_note,
                'note' => $note
            ],
            [
                'slug' => $slug,
                'numero' => $numero
            ]
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = strtolower(trim($slug));
        return $this;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getJacquette(): ?int
    {
        return $this->jacquette;
    }

    public function setJacquette(?int $jacquette): self
    {
        $this->jacquette = $jacquette;
        return $this;
    }

    public function getLivreNote(): ?int
    {
        return $this->livre_note;
    }

    public function setLivreNote(?int $livre_note): self
    {
        $this->livre_note = $livre_note;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getLivre(): string
    {
        return $this->livre;
    }

    public function setLivre(string $livre): self
    {
        $this->livre = trim($livre);
        return $this;
    }
}