<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class FrenchModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column vocabulary */
    protected string $vocabulary;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'french';
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
     * getter vocabulary
     * @return string
     */
    public function getVocabulary(): string
    {
        return $this->vocabulary;
    }

    /**
     * setter vocabulary
     * @param string $vocabulary
     * @return self
     */
    public function setVocabulary(string $vocabulary): self
    {
        $this->vocabulary = $vocabulary;
        return $this;
    }
}