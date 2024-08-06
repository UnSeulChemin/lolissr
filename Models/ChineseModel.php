<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class ChineseModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column word */
    protected string $word;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'chinese';
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
     * getter word
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * setter word
     * @param string $word
     * @return self
     */
    public function setWord(string $word): self
    {
        $this->word = $word;
        return $this;
    }
}