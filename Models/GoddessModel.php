<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class GoddessModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* image thumbnail */
    protected string $thumbnail;

    /* image extension */
    protected string $extension;

    /* column character */
    protected string $character;

    /* column serie */
    protected string $serie;

    /* column rarity */
    protected string $rarity;

    /* column set */
    protected string $set;

    /* column date */
    protected string $date;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'goddess';
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
     * getter serie
     * @return string
     */
    public function getSerie(): string
    {
        return $this->serie;
    }

    /**
     * setter serie
     * @param string $serie
     * @return self
     */
    public function setSerie(string $serie): self
    {
        $this->serie = $serie;
        return $this;
    }

    /**
     * getter rarity
     * @return string
     */
    public function getRarity(): string
    {
        return $this->rarity;
    }

    /**
     * setter rarity
     * @param string $rarity
     * @return self
     */
    public function setRarity(string $rarity): self
    {
        $this->rarity = $rarity;
        return $this;
    }

    /**
     * getter set
     * @return string
     */
    public function getSet(): string
    {
        return $this->set;
    }

    /**
     * setter set
     * @param string $set
     * @return self
     */
    public function setSet(string $set): self
    {
        $this->set = $set;
        return $this;
    }

    /**
     * getter date
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * setter date
     * @param string $date
     * @return self
     */
    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }
}