<?php

namespace App\Models\Trait;

trait CreatedAtTrait
{
    /**
     * Date de création.
     */
    protected ?\DateTimeImmutable $created_at = null;

    /**
     * Retourne la date de création brute.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * Retourne la date formatée.
     * Format par défaut : d/m/Y H:i
     */
    public function getCreatedAtFormatted(string $format = 'd/m/Y H:i'): ?string
    {
        return $this->created_at?->format($format);
    }

    /**
     * Définit la date de création.
     */
    public function setCreatedAt(?string $created_at): self
    {
        if ($created_at === null || $created_at === '')
        {
            $this->created_at = null;
            return $this;
        }

        try
        {
            $this->created_at = new \DateTimeImmutable($created_at);
        }
        catch (\Exception)
        {
            $this->created_at = null;
        }

        return $this;
    }
}