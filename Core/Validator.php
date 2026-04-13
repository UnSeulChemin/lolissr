<?php

namespace App\Core;

class Validator
{
    /**
     * Données à valider.
     */
    private array $data;

    /**
     * Fichiers à valider.
     */
    private array $files;

    /**
     * Liste des erreurs.
     */
    private array $errors = [];

    /**
     * Champs autorisés à être vides.
     */
    private array $nullable = [];

    public function __construct(array $data, array $files = [])
    {
        $this->data = $data;
        $this->files = $files;
    }

    /**
     * Vérifie si un champ a déjà une erreur.
     */
    private function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Vérifie si un champ nullable est vide.
     */
    private function isNullableAndEmpty(string $field): bool
    {
        if (!in_array($field, $this->nullable, true))
        {
            return false;
        }

        $value = trim((string) ($this->data[$field] ?? ''));

        return $value === '';
    }

    /**
     * Vérifie si une validation doit être ignorée.
     */
    private function shouldSkip(string $field): bool
    {
        return $this->hasError($field) || $this->isNullableAndEmpty($field);
    }

    /**
     * Autorise un champ vide.
     */
    public function nullable(string $field): self
    {
        if (!in_array($field, $this->nullable, true))
        {
            $this->nullable[] = $field;
        }

        return $this;
    }

    /**
     * Champ obligatoire.
     */
    public function required(string $field, ?string $message = null): self
    {
        if ($this->hasError($field))
        {
            return $this;
        }

        $value = trim((string) ($this->data[$field] ?? ''));

        if ($value === '')
        {
            $this->errors[$field] = $message ?? "Le champ {$field} est obligatoire.";
        }

        return $this;
    }

    /**
     * Vérifie que c'est une chaîne.
     */
    public function string(string $field, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (!is_string($value))
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être une chaîne.";
        }

        return $this;
    }

    /**
     * Entier valide.
     */
    public function integer(string $field, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (filter_var($value, FILTER_VALIDATE_INT) === false)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être un entier.";
        }

        return $this;
    }

    /**
     * Valeur minimale.
     */
    public function min(string $field, int $min, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être supérieur ou égal à {$min}.";
        }

        return $this;
    }

    /**
     * Valeur maximale.
     */
    public function max(string $field, int $max, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value > $max)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être inférieur ou égal à {$max}.";
        }

        return $this;
    }

    /**
     * Longueur maximale.
     */
    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = trim((string) ($this->data[$field] ?? ''));

        if (mb_strlen($value) > $max)
        {
            $this->errors[$field] = $message ?? "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }

        return $this;
    }

    /**
     * Vérifie qu'une valeur fait partie d'une liste autorisée.
     */
    public function in(string $field, array $allowedValues, ?string $message = null): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (!in_array($value, $allowedValues, true))
        {
            $this->errors[$field] = $message ?? "Valeur invalide pour {$field}.";
        }

        return $this;
    }

    /**
     * Vérifie qu'un fichier a été envoyé.
     */
    public function fileRequired(string $field, ?string $message = null): self
    {
        if ($this->hasError($field))
        {
            return $this;
        }

        if (
            !isset($this->files[$field]) ||
            !isset($this->files[$field]['error']) ||
            $this->files[$field]['error'] === UPLOAD_ERR_NO_FILE
        )
        {
            $this->errors[$field] = $message ?? "Le fichier {$field} est obligatoire.";
        }

        return $this;
    }

    /**
     * Vérifie que l'upload du fichier s'est bien passé.
     */
    public function fileOk(string $field, ?string $message = null): self
    {
        if ($this->hasError($field))
        {
            return $this;
        }

        if (!isset($this->files[$field]) || !isset($this->files[$field]['error']))
        {
            $this->errors[$field] = $message ?? "Fichier {$field} introuvable.";
            return $this;
        }

        if ($this->files[$field]['error'] !== UPLOAD_ERR_OK)
        {
            $this->errors[$field] = $message ?? "Erreur lors de l'envoi du fichier {$field}.";
        }

        return $this;
    }

    /**
     * Vérifie l'extension d'une image.
     */
    public function imageExtension(string $field, array $allowedExtensions, ?string $message = null): self
    {
        if ($this->hasError($field))
        {
            return $this;
        }

        $name = $this->files[$field]['name'] ?? null;

        if (!is_string($name) || $name === '')
        {
            $this->errors[$field] = $message ?? "Extension invalide pour {$field}.";
            return $this;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        if (!in_array($extension, $allowedExtensions, true))
        {
            $this->errors[$field] = $message ?? "Format de fichier non autorisé pour {$field}.";
        }

        return $this;
    }

    /**
     * Vérifie la taille maximale d'un fichier.
     */
    public function maxFileSize(string $field, int $maxBytes, ?string $message = null): self
    {
        if ($this->hasError($field))
        {
            return $this;
        }

        $size = $this->files[$field]['size'] ?? null;

        if (!is_int($size) && !ctype_digit((string) $size))
        {
            $this->errors[$field] = $message ?? "Taille invalide pour {$field}.";
            return $this;
        }

        if ((int) $size > $maxBytes)
        {
            $this->errors[$field] = $message ?? "Le fichier {$field} dépasse la taille autorisée.";
        }

        return $this;
    }

    /**
     * Retourne toutes les erreurs.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Indique si la validation a échoué.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
}