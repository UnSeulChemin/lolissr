<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class MangaModel extends Model
{
    use CreatedAtTrait;

    protected int $id;
    protected string $thumbnail;
    protected string $extension;
    protected string $slug;
    protected string $numero;
    protected string $livre;

    public function __construct()
    {
        $this->table = 'manga';
    }

    public function findAllFirstTomes(string $orderBy, int $eachPerPage, int $page): array
    {
        $start = ($page - 1) * $eachPerPage;
        $orderBy = in_array($orderBy, ['id DESC', 'id ASC']) ? $orderBy : 'id DESC';

        $sql = "SELECT * 
                FROM {$this->table}
                WHERE numero = :numero
                ORDER BY $orderBy
                LIMIT $start, $eachPerPage";

        return $this->requete($sql, [
            'numero' => '01'
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
        $query = $this->requete(
            "SELECT * FROM {$this->table}
            WHERE slug = ?
            AND numero = ?",
            [
                strtolower(trim($slug)),
                $numero
            ]
        );

        return $query->fetch();
    }

public function insert(array $data)
{
    return $this->requete(
        "INSERT INTO {$this->table} (thumbnail, extension, slug, livre, numero, created_at)
         VALUES (:thumbnail, :extension, :slug, :livre, :numero, NOW())",
        [
            'thumbnail' => $data['thumbnail'],
            'extension' => $data['extension'],
            'slug' => $data['slug'],
            'livre' => $data['livre'],
            'numero' => $data['numero']
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;
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