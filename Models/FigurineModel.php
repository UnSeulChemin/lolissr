<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class FigurineModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* image thumbnail */
    protected string $thumbnail;

    /* image extension */
    protected string $extension;

    /* column origin */
    protected string $origin;

    /* column character */
    protected string $character;

    /* column company */
    protected string $company;

    /* column price */
    protected int $price;

    /* column release */
    protected string $release;

    /* column link */
    protected string $link;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'figurine';
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
     * getter origin
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * setter origin
     * @param string $origin
     * @return self
     */
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * getter character
     * @return string
     */
    public function getCharacter(): string
    {
        return $this->character;
    }

    /**
     * setter character
     * @param string $character
     * @return self
     */
    public function setCharacter(string $character): self
    {
        $this->character = $character;
        return $this;
    }

    /**
     * getter company
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * setter company
     * @param string $company
     * @return self
     */
    public function setCompany(string $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * getter price
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * setter price
     * @param int $price
     * @return self
     */
    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * getter release
     * @return string
     */
    public function getRelease(): string
    {
        return $this->release;
    }

    /**
     * setter release
     * @param string $release
     * @return self
     */
    public function setRelease(string $release): self
    {
        $this->release = $release;
        return $this;
    }

    /**
     * getter link
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * setter link
     * @param string $link
     * @return self
     */
    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }
}