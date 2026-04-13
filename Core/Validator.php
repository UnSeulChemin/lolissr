<?php

namespace App\Core;

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Champ obligatoire
     */
    public function required(string $field, ?string $message = null): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));

        if ($value === '')
        {
            $this->errors[$field] = $message ?? "Le champ {$field} est obligatoire.";
        }

        return $this;
    }

    /**
     * Entier valide
     */
    public function integer(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || filter_var($value, FILTER_VALIDATE_INT) === false)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être un entier.";
        }

        return $this;
    }

    /**
     * Valeur minimale
     */
    public function min(string $field, int $min, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être supérieur ou égal à {$min}.";
        }

        return $this;
    }

    /**
     * Longueur maximale
     */
    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));

        if ($value !== '' && mb_strlen($value) > $max)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }

        return $this;
    }

    /**
     * Vérifie qu'une valeur fait partie d'une liste autorisée
     */
    public function in(string $field, array $allowedValues, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if (!in_array($value, $allowedValues, true))
        {
            $this->errors[$field] = $message ?? "Valeur invalide pour {$field}.";
        }

        return $this;
    }

    /**
     * Retourne toutes les erreurs
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Indique si la validation a échoué
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
}