<?php

declare(strict_types=1);

namespace Framework\Http;

final class UploadedFile
{
    public function __construct(
        private Request $request,
    ) {
    }

    /**
     * Vérifie si un fichier uploadé existe.
     */
    public function exists(string $key): bool
    {
        return $this->request->hasFile($key)
            && $this->request->fileError($key) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Retourne le nom original du fichier.
     */
    public function name(string $key): ?string
    {
        if (!$this->exists($key)) {
            return null;
        }

        $name = $this->request->fileName($key);

        return $name !== '' ? $name : null;
    }

    /**
     * Retourne le chemin temporaire du fichier.
     */
    public function tmp(string $key): ?string
    {
        if (!$this->exists($key)) {
            return null;
        }

        $tmp = $this->request->fileTmpPath($key);

        return $tmp !== '' ? $tmp : null;
    }

    /**
     * Retourne le code d'erreur du fichier.
     */
    public function error(string $key): ?int
    {
        if (!$this->request->hasFile($key)) {
            return null;
        }

        return $this->request->fileError($key);
    }

    /**
     * Retourne la taille du fichier.
     */
    public function size(string $key): ?int
    {
        if (!$this->exists($key)) {
            return null;
        }

        return $this->request->fileSize($key);
    }

    /**
     * Retourne l'extension du fichier.
     */
    public function extension(string $key): ?string
    {
        $name = $this->name($key);

        if ($name === null) {
            return null;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $extension !== ''
            ? $extension
            : null;
    }

    /**
     * Retourne le type MIME réel du fichier.
     */
    public function mimeType(string $key): ?string
    {
        $tmpName = $this->tmp($key);

        if ($tmpName === null || !is_file($tmpName)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file($finfo, $tmpName);

        finfo_close($finfo);

        return is_string($mimeType) && $mimeType !== ''
            ? strtolower($mimeType)
            : null;
    }
}
