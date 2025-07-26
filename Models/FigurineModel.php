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

    /* column serie */
    protected string $serie;

    /* column brand */
    protected string $brand;

    /* column price */
    protected int $price;

    /* column date */
    protected string $date;

    /* column obtained */
    protected string $obtained;

    /* column estimated 'delivery' */
    protected string $estimated;

    /* column link */
    protected string $link;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'figurine';
    }

    /**
     * model->findAllPaginateIsObtained('id DESC', 8, 1);
     * @param string $orderBy
     * @param integer $eachPerPage
     * @param integer $getId
     * @return array
     */
    public function findAllPaginateIsObtained(string $orderBy, int $eachPerPage, int $getId): array
    {
        $start = ($getId -1) * $eachPerPage;
    
        $query = $this->requete("SELECT * FROM {$this->table} WHERE obtained = 'Y' ORDER BY $orderBy LIMIT " . $start . ", " . $eachPerPage);
        return $query->fetchAll();
    }

    /**
     * model->countPaginate(8)
     * @param integer $eachPerPage
     * @return integer
     */
    public function countPaginateIsObtained(int $eachPerPage): int
    {
        $query = $this->requete("SELECT COUNT(*) AS `count` FROM {$this->table} WHERE obtained = 'Y'");

        if ($query->rowCount() > 0) { $countTotal = $query->fetch(); }

        $counts = ceil($countTotal->count / $eachPerPage);
        return $counts;
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
     * getter obtained
     * @return string
     */
    public function getObtained(): string
    {
        return $this->obtained;
    }

    /**
     * setter obtained
     * @param string $obtained
     * @return self
     */
    public function setObtained(string $obtained): self
    {
        $this->obtained = $obtained;
        return $this;
    }

    /**
     * getter estimated
     * @return string
     */
    public function getEstimated(): string
    {
        return $this->estimated;
    }

    /**
     * setter estimated
     * @param string $estimated
     * @return self
     */
    public function setEstimated(string $estimated): self
    {
        $this->estimated = $estimated;
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