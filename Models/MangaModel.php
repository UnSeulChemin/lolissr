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
    protected ?int $note = null;
    protected string $livre;

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

    return (int) ceil(($result->total ?? 0) / $eachPerPage);
}

    public function findAllFirstTomes(string $orderBy, int $eachPerPage, int $page): array
    {
        $page = max(1, $page);
        $eachPerPage = max(1, $eachPerPage);
        $start = ($page - 1) * $eachPerPage;
        $orderBy = in_array($orderBy, ['id DESC', 'id ASC'], true) ? $orderBy : 'id DESC';

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

    public function findBySlug(string $slug): array
    {
        $sql = "SELECT * 
                FROM {$this->table}
                WHERE slug = :slug
                ORDER BY numero DESC";

        return $this->requete($sql, [
            'slug' => strtolower(trim($slug))
        ])->fetchAll();
    }

    public function findOneBySlugAndNumero(string $slug, int $numero)
    {
        return $this->requete(
            "SELECT * 
             FROM {$this->table}
             WHERE slug = ?
             AND numero = ?",
            [
                strtolower(trim($slug)),
                $numero
            ]
        )->fetch();
    }

    public function insert(array $datas): bool
    {
        return $this->requete(
            "INSERT INTO {$this->table} (thumbnail, extension, slug, livre, numero, created_at)
             VALUES (:thumbnail, :extension, :slug, :livre, :numero, NOW())",
            [
                'thumbnail' => $datas['thumbnail'],
                'extension' => $datas['extension'],
                'slug' => $datas['slug'],
                'livre' => $datas['livre'],
                'numero' => $datas['numero']
            ]
        ) !== false;
    }

    public function updateNote(string $slug, int $numero, ?int $note): bool
    {
        return $this->requete(
            "UPDATE {$this->table}
             SET note = :note
             WHERE slug = :slug
             AND numero = :numero",
            [
                'note' => $note,
                'slug' => strtolower(trim($slug)),
                'numero' => $numero
            ]
        ) !== false;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
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

    public function getLivre(): string
    {
        return $this->livre;
    }

    public function setLivre(string $livre): self
    {
        $this->livre = $livre;
        return $this;
    }
}