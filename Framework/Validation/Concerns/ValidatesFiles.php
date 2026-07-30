<?php

declare(strict_types=1);

namespace Framework\Validation\Concerns;

trait ValidatesFiles
{
    // =========================================
    // PRÉSENCE
    // =========================================

    public function fileRequired(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->hasError($field))
        {
            return $this;
        }

        if (! $this->hasUploadedFile($field))
        {
            $this->addError(
                $field,
                $message ?? "Le fichier {$field} est obligatoire."
            );
        }

        return $this;
    }

    public function fileOk(string $field, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->hasError($field))
        {
            return $this;
        }

        $file = $this->fileData($field);

        if ($file === null)
        {
            return $this;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE)
        {
            return $this;
        }

        if ($error !== UPLOAD_ERR_OK)
        {
            $this->addError(
                $field,
                $message ?? "Erreur lors de l'envoi du fichier {$field}."
            );
        }

        return $this;
    }

    // =========================================
    // FORMAT
    // =========================================

    /**
     * @param list<string> $allowedExtensions
     */
    public function imageExtension(
        string $field,
        array $allowedExtensions,
        ?string $message = null
    ): self {
        $this->rememberField($field);

        if ($this->shouldSkipFile($field))
        {
            return $this;
        }

        $file = $this->fileData($field);
        $name = $file['name'] ?? null;

        if (! is_string($name) || trim($name) === '')
        {
            $this->addError(
                $field,
                $message ?? "Extension invalide pour {$field}."
            );

            return $this;
        }

        $allowedExtensions = array_values(
            array_unique(
                array_map(
                    static function (string $extension): string
                    {
                        $extension = strtolower(trim($extension));

                        return $extension === 'jpeg' ? 'jpg' : $extension;
                    },
                    $allowedExtensions
                )
            )
        );

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if ($extension === '' || ! in_array($extension, $allowedExtensions, true))
        {
            $this->addError(
                $field,
                $message ?? "Format de fichier non autorisé pour {$field}."
            );
        }

        return $this;
    }

    /**
     * @param list<string> $allowedMimeTypes
     */
    public function imageMime(
        string $field,
        array $allowedMimeTypes,
        ?string $message = null
    ): self {
        $this->rememberField($field);

        if ($this->shouldSkipFile($field))
        {
            return $this;
        }

        $file = $this->fileData($field);
        $temporaryPath = $file['tmp_name'] ?? null;

        if (
            ! is_string($temporaryPath)
            || $temporaryPath === ''
            || ! is_file($temporaryPath)
        ) {
            $this->addError(
                $field,
                $message ?? "Fichier temporaire invalide pour {$field}."
            );

            return $this;
        }

        $allowedMimeTypes = array_values(
            array_unique(
                array_map(
                    static fn (string $mimeType): string => strtolower(trim($mimeType)),
                    $allowedMimeTypes
                )
            )
        );

        $mimeType = @$this->finfo->file($temporaryPath);

        if (! is_string($mimeType))
        {
            $this->addError(
                $field,
                $message ?? "Type MIME non autorisé pour {$field}."
            );

            return $this;
        }

        $mimeType = strtolower(trim($mimeType));

        if ($mimeType === '' || ! in_array($mimeType, $allowedMimeTypes, true))
        {
            $this->addError(
                $field,
                $message ?? "Type MIME non autorisé pour {$field}."
            );
        }

        return $this;
    }

    // =========================================
    // TAILLE
    // =========================================

    public function maxFileSize(string $field, int $maxBytes, ?string $message = null): self
    {
        $this->rememberField($field);

        if ($this->shouldSkipFile($field))
        {
            return $this;
        }

        $file = $this->fileData($field);
        $size = $file['size'] ?? null;
        $validatedSize = filter_var($size, FILTER_VALIDATE_INT);

        if ($validatedSize === false || $validatedSize < 0)
        {
            $this->addError(
                $field,
                $message ?? "Taille invalide pour {$field}."
            );

            return $this;
        }

        if ($validatedSize > $maxBytes)
        {
            $this->addError(
                $field,
                $message ?? "Le fichier {$field} dépasse la taille autorisée."
            );
        }

        return $this;
    }
}