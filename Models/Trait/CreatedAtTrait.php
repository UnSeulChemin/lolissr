<?php

namespace App\Models\Trait;

use DateTimeImmutable;

trait CreatedAtTrait
{
    /**
     * date de création
     */
    protected ?DateTimeImmutable $created_at = null;

    /**
     * retourne la date de création
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * définit la date de création
     */
    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = new DateTimeImmutable($created_at);
        return $this;
    }
}