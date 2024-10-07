<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class FrenchModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column word */
    protected string $word;

    /* column type */
    protected string $type;

    /* column definition */
    protected string $definition;

    /* column example */
    protected string $example;

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
     * getter definition
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * setter definition
     * @param string $definition
     * @return self
     */
    public function setDefinition(string $definition): self
    {
        $this->definition = $definition;
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