<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class NendoroidModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* image thumbnail */
    protected string $thumbnail;

    /* image extension */
    protected string $extension;

    /* column serie */
    protected string $serie;

    /* column brand */
    protected string $brand;

    /* column price */
    protected int $price;

    /* column date */
    protected string $date;

    /* column stock */
    protected string $stock;

    /* column love */
    protected string $love;

    /* column hololive */
    protected string $hololive;

    /* column link */
    protected string $link;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'nendoroid';
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
     * getter brand
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * setter brand
     * @param string $brand
     * @return self
     */
    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
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

    /**
     * getter stock
     * @return string
     */
    public function getStock(): string
    {
        return $this->stock;
    }

    /**
     * setter stock
     * @param string $stock
     * @return self
     */
    public function setStock(string $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * getter love
     * @return string
     */
    public function getLove(): string
    {
        return $this->love;
    }

    /**
     * setter love
     * @param string $love
     * @return self
     */
    public function setLove(string $love): self
    {
        $this->love = $love;
        return $this;
    }

    /**
     * getter hololive
     * @return string
     */
    public function getHololive(): string
    {
        return $this->hololive;
    }

    /**
     * setter hololive
     * @param string $hololive
     * @return self
     */
    public function setHololive(string $hololive): self
    {
        $this->hololive = $hololive;
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