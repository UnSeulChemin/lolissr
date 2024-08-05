<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class GoddessModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column quality */
    protected string $quality;

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
     * getter quality
     * @return string
     */
    public function getQuality(): string
    {
        return $this->quality;
    }

    /**
     * setter quality
     * @param string $quality
     * @return self
     */
    public function setQuality(string $quality): self
    {
        $this->quality = $quality;
        return $this;
    }
}