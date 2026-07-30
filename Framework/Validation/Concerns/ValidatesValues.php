<?php

declare(strict_types=1);

namespace Framework\Validation\Concerns;

trait ValidatesValues
{
    // =========================================
    // PRÉSENCE
    // =========================================

    public function nullable(string $field): self
    {
        $this->rememberField($field);
        $this->nullable[$field] = true;

        return $this;
    }

    public function required(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->hasError($field))
        {
            return $this;
        }

        if ($this->isEmptyValue($this->value($field)))
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} est obligatoire."
            );
        }

        return $this;
    }

    // =========================================
    // TYPES
    // =========================================

    public function string(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        if (! is_string($this->value($field)))
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être une chaîne."
            );
        }

        return $this;
    }

    public function integer(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        if ($this->integerValue($field) === null)
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être un entier."
            );
        }

        return $this;
    }

    public function numeric(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        if ($this->numericValue($field) === null)
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être un nombre."
            );
        }

        return $this;
    }

    // =========================================
    // BORNES
    // =========================================

    public function min(string $field, int|float $min, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->numericValue($field);

        if ($value === null || $value < $min)
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être supérieur ou égal à {$min}."
            );
        }

        return $this;
    }

    public function max(string $field, int|float $max, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->numericValue($field);

        if ($value === null || $value > $max)
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être inférieur ou égal à {$max}."
            );
        }

        return $this;
    }

    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->value($field);

        if (! is_string($value))
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} doit être une chaîne."
            );

            return $this;
        }

        if (mb_strlen($value) > $max)
        {
            $this->addError(
                $field,
                $message ?? "Le champ {$field} ne doit pas dépasser {$max} caractères."
            );
        }

        return $this;
    }

    // =========================================
    // VALEURS AUTORISÉES
    // =========================================

    /**
     * @param list<string> $allowedValues
     */
    public function in(string $field, array $allowedValues, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkip($field))
        {
            return $this;
        }

        if (! in_array($this->value($field), $allowedValues, true))
        {
            $this->addError(
                $field,
                $message ?? "Valeur invalide pour {$field}."
            );
        }

        return $this;
    }
}