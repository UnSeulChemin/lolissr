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

    /* column type */
    protected string $type;

    /* column 'translate' french */
    protected string $french;

    /* column 'translate' english */
    protected string $english;

    /* column example */
    protected string $example;

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

    /**
     * getter type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * setter type
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * getter french
     * @return string
     */
    public function getFrench(): string
    {
        return $this->french;
    }

    /**
     * setter french
     * @param string $french
     * @return self
     */
    public function setFrench(string $french): self
    {
        $this->french = $french;
        return $this;
    }

    /**
     * getter english
     * @return string
     */
    public function getEnglish(): string
    {
        return $this->english;
    }

    /**
     * setter english
     * @param string $english
     * @return self
     */
    public function setEnglish(string $english): self
    {
        $this->english = $english;
        return $this;
    }

    /**
     * getter example
     * @return string
     */
    public function getExample(): string
    {
        return $this->example;
    }

    /**
     * setter example
     * @param string $example
     * @return self
     */
    public function setExample(string $example): self
    {
        $this->example = $example;
        return $this;
    }
}