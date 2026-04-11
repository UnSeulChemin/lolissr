<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class ChineseModel extends Model
{
    use CreatedAtTrait;

    protected string $table = 'chinese';

    protected int $id;
    protected string $word;
    protected string $type;
    protected string $french;
    protected string $english;
    protected string $example;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFrench(): string
    {
        return $this->french;
    }

    public function setFrench(string $french): self
    {
        $this->french = $french;
        return $this;
    }

    public function getEnglish(): string
    {
        return $this->english;
    }

    public function setEnglish(string $english): self
    {
        $this->english = $english;
        return $this;
    }

    public function getExample(): string
    {
        return $this->example;
    }

    public function setExample(string $example): self
    {
        $this->example = $example;
        return $this;
    }
}