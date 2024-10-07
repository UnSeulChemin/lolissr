<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class MangaModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column manga */
    protected string $manga;

    /* column 'publishing' house */
    protected string $house;

    /* column tome */
    protected string $tome;

    /* column 'in future' next */
    protected string $next;

    /* column end */
    protected string $end;

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
     * getter manga
     * @return string
     */
    public function getManga(): string
    {
        return $this->manga;
    }

    /**
     * setter manga
     * @param string $manga
     * @return self
     */
    public function setManga(string $manga): self
    {
        $this->manga = $manga;
        return $this;
    }

    /**
     * getter house
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * setter house
     * @param string $house
     * @return self
     */
    public function setHouse(string $house): self
    {
        $this->house = $house;
        return $this;
    }

    /**
     * getter tome
     * @return string
     */
    public function getTome(): string
    {
        return $this->tome;
    }

    /**
     * setter tome
     * @param string $tome
     * @return self
     */
    public function setTome(string $tome): self
    {
        $this->tome = $tome;
        return $this;
    }

    /**
     * getter next
     * @return string
     */
    public function getNext(): string
    {
        return $this->next;
    }

    /**
     * setter next
     * @param string $next
     * @return self
     */
    public function setNext(string $next): self
    {
        $this->next = $next;
        return $this;
    }

    /**
     * getter end
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * setter end
     * @param string $end
     * @return self
     */
    public function setEnd(string $end): self
    {
        $this->end = $end;
        return $this;
    }
}