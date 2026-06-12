<?php

declare(strict_types=1);

namespace Framework\Validation;

use finfo;

final class Validator
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $files;

    /**
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * @var array<string, true>
     */
    private array $nullable = [];

    /**
     * @var array<string, ?int>
     */
    private array $integerCache = [];

    private finfo $finfo;

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<string, mixed>> $files
     */
    public function __construct(
        array $data,
        array $files = [],
    ) {
        $this->data = $data;
        $this->files = $files;

        $this->finfo = new finfo(
            FILEINFO_MIME_TYPE,
        );
    }

    private function hasError(
        string $field,
    ): bool {
        return isset($this->errors[$field]);
    }

    private function isNullableAndEmpty(
        string $field,
    ): bool
    {
        if (
            ! isset(
                $this->nullable[$field],
            )
        ) {
            return false;
        }

        $value = trim(
            (string) (
                $this->data[$field]
                ?? ''
            ),
        );

        return $value === '';
    }

    private function shouldSkip(
        string $field,
    ): bool {
        return $this->hasError($field)
            || $this->isNullableAndEmpty($field);
    }

    private function hasUploadedFile(
        string $field,
    ): bool {
        return isset(
            $this->files[$field]['tmp_name'],
        )
        && is_string(
            $this->files[$field]['tmp_name'],
        )
        && $this->files[$field]['tmp_name'] !== '';
    }

    private function shouldSkipFile(
        string $field,
    ): bool {
        return $this->hasError($field)
            || ! $this->hasUploadedFile($field);
    }

    public function nullable(
    string $field,
    ): self
    {
        $this->nullable[$field] = true;

        return $this;
    }

    public function required(
        string $field,
        ?string $message = null,
    ): self {
        if ($this->hasError($field)) {
            return $this;
        }

        $value = trim(
            (string) ($this->data[$field] ?? ''),
        );

        if ($value === '') {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} est obligatoire.";
        }

        return $this;
    }

    public function string(
        string $field,
        ?string $message = null,
    ): self {
        if ($this->shouldSkip($field)) {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (!is_string($value)) {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} doit être une chaîne.";
        }

        return $this;
    }

    public function integer(
        string $field,
        ?string $message = null,
    ): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        if (
            $this->integerValue($field)
            === null
        ) {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} doit être un entier.";
        }

        return $this;
    }

    private function integerValue(
        string $field,
    ): ?int
    {
        if (
            array_key_exists(
                $field,
                $this->integerCache,
            )
        ) {
            return $this->integerCache[$field];
        }

        $value =
            $this->data[$field]
            ?? null;

        return $this->integerCache[$field] =
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
            ) !== false
                ? (int) $value
                : null;
    }

    public function min(
        string $field,
        int $min,
        ?string $message = null,
    ): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value =
            $this->integerValue(
                $field,
            );

        if (
            $value === null
            || $value < $min
        ) {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} doit être supérieur ou égal à {$min}.";
        }

        return $this;
    }

    public function max(
        string $field,
        int $max,
        ?string $message = null,
    ): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value =
            $this->integerValue(
                $field,
            );

        if (
            $value === null
            || $value > $max
        ) {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} doit être inférieur ou égal à {$max}.";
        }

        return $this;
    }

    public function maxLength(
        string $field,
        int $max,
        ?string $message = null,
    ): self
    {
        if ($this->shouldSkip($field))
        {
            return $this;
        }

        $value =
            (string) (
                $this->data[$field]
                ?? ''
            );

        if (
            mb_strlen($value)
            > $max
        ) {
            $this->errors[$field] =
                $message
                ?? "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }

        return $this;
    }

    /**
     * @param list<string> $allowedValues
     */
    public function in(
        string $field,
        array $allowedValues,
        ?string $message = null,
    ): self {
        if ($this->shouldSkip($field)) {
            return $this;
        }

        $value = $this->data[$field] ?? null;

        if (
            !in_array(
                $value,
                $allowedValues,
                true,
            )
        ) {
            $this->errors[$field] =
                $message
                ?? "Valeur invalide pour {$field}.";
        }

        return $this;
    }

    public function fileRequired(
        string $field,
        ?string $message = null,
    ): self {
        if ($this->hasError($field)) {
            return $this;
        }

        if (! $this->hasUploadedFile($field)) {
            $this->errors[$field] =
                $message
                ?? "Le fichier {$field} est obligatoire.";
        }

        return $this;
    }

    public function fileOk(
        string $field,
        ?string $message = null,
    ): self {
        if ($this->hasError($field)) {
            return $this;
        }

        if (! $this->hasUploadedFile($field)) {
            return $this;
        }

        $error = $this->files[$field]['error'] ?? null;

        if ($error !== UPLOAD_ERR_OK) {
            $this->errors[$field] =
                $message
                ?? "Erreur lors de l'envoi du fichier {$field}.";
        }

        return $this;
    }

    /**
     * @param list<string> $allowedExtensions
     */
    public function imageExtension(
        string $field,
        array $allowedExtensions,
        ?string $message = null,
    ): self {
        if ($this->shouldSkipFile($field)) {
            return $this;
        }

        $name =
            $this->files[$field]['name']
            ?? null;

        if (
            ! is_string($name)
            || $name === ''
        ) {
            $this->errors[$field] =
                $message
                ?? "Extension invalide pour {$field}.";

            return $this;
        }

        $allowedExtensions =
            array_map(
                'strtolower',
                $allowedExtensions,
            );

        $extension = strtolower(
            pathinfo(
                $name,
                PATHINFO_EXTENSION,
            ),
        );

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (
            ! in_array(
                $extension,
                $allowedExtensions,
                true,
            )
        ) {
            $this->errors[$field] =
                $message
                ?? "Format de fichier non autorisé pour {$field}.";
        }

        return $this;
    }

    /**
     * @param list<string> $allowedMimeTypes
     */
    public function imageMime(
        string $field,
        array $allowedMimeTypes,
        ?string $message = null,
    ): self {
        if ($this->shouldSkipFile($field)) {
            return $this;
        }

        $tmpName =
            $this->files[$field]['tmp_name']
            ?? null;

        if (
            ! is_string($tmpName)
            || $tmpName === ''
            || ! is_file($tmpName)
        ) {
            $this->errors[$field] =
                $message
                ?? "Fichier temporaire invalide pour {$field}.";

            return $this;
        }

        $allowedMimeTypes =
            array_map(
                'strtolower',
                $allowedMimeTypes,
            );

        $mimeType =
            $this->finfo->file(
                $tmpName,
            );

        if (
            ! is_string($mimeType)
            || ! in_array(
                strtolower($mimeType),
                $allowedMimeTypes,
                true,
            )
        ) {
            $this->errors[$field] =
                $message
                ?? "Type MIME non autorisé pour {$field}.";
        }

        return $this;
    }

    public function maxFileSize(
        string $field,
        int $maxBytes,
        ?string $message = null,
    ): self {
        if ($this->shouldSkipFile($field)) {
            return $this;
        }

        $size = $this->files[$field]['size'] ?? null;

        if (
            !is_int($size)
            && !ctype_digit((string) $size)
        ) {
            $this->errors[$field] =
                $message
                ?? "Taille invalide pour {$field}.";

            return $this;
        }

        if ((int) $size > $maxBytes) {
            $this->errors[$field] =
                $message
                ?? "Le fichier {$field} dépasse la taille autorisée.";
        }

        return $this;
    }

    public function passes(): bool
    {
        return ! $this->fails();
    }

    public function error(
        string $field,
    ): ?string
    {
        return $this->errors[$field]
            ?? null;
    }

    public function firstError(): ?string
    {
        $first =
            reset(
                $this->errors,
            );

        return $first !== false
            ? $first
            : null;
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }
}