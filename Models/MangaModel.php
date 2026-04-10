<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class MangaModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* image thumbnail */
    protected string $thumbnail;

    /* image extension */
    protected string $extension;

    /* column livre */
    protected string $livre;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'manga';
    }

    /**
     * getter id
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * setter id
     * @param integer $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getter thumbnail
     * @return string
     */
    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    /**
     * setter thumbnail
     * @param string $thumbnail
     * @return self
     */
    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    /**
     * getter extension
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * setter extension
     * @param string $extension
     * @return self
     */
    public function setExtension(string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * getter livre
     * @return string
     */
    public function getLivre(): string
    {
        return $this->livre;
    }

    /**
     * setter livre
     * @param string $livre
     * @return self
     */
    public function setLivre(string $livre): self
    {
        $this->livre = $livre;
        return $this;
    }
}