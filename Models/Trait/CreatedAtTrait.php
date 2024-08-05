<?php
namespace App\Models\Trait;

use DateTimeImmutable;

Trait CreatedAtTrait
{
    protected $created_at;

    /**
     * getter created_at
     * @return DateTimeImmutable
     */
    public function getCreated_at(): DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * setter created_at
     * @param string $created_at
     * @return self
     */
    public function setCreated_at(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
}