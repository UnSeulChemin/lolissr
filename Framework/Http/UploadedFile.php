<?php

declare(strict_types=1);

namespace Framework\Http;

use finfo;

final readonly class UploadedFile
{
    private finfo $finfo;

    public function __construct(private Request $request)
    {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
    }

    // =========================================
    // FICHIER
    // =========================================

    public function exists(string $key): bool
    {
        return $this->uploadedFile($key) !== null;
    }

    public function name(string $key): ?string
    {
        $file = $this->uploadedFile($key);

        if ($file === null)
        {
            return null;
        }

        $name = trim((string) ($file['name'] ?? ''));

        return $name !== '' ? $name : null;
    }

    public function tmp(string $key): ?string
    {
        $file = $this->uploadedFile($key);

        if ($file === null)
        {
            return null;
        }

        $temporaryPath = trim((string) ($file['tmp_name'] ?? ''));

        return $temporaryPath !== '' ? $temporaryPath : null;
    }

    public function error(string $key): ?int
    {
        $file = $this->file($key);

        if ($file === null)
        {
            return null;
        }

        return (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    public function size(string $key): ?int
    {
        $file = $this->uploadedFile($key);

        if ($file === null)
        {
            return null;
        }

        return max(0, (int) ($file['size'] ?? 0));
    }

    public function extension(string $key): ?string
    {
        $name = $this->name($key);

        if ($name === null)
        {
            return null;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($extension === '')
        {
            return null;
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    public function mimeType(string $key): ?string
    {
        $temporaryPath = $this->tmp($key);

        if ($temporaryPath === null || ! is_file($temporaryPath))
        {
            return null;
        }

        $mimeType = @$this->finfo->file($temporaryPath);

        if (! is_string($mimeType))
        {
            return null;
        }

        $mimeType = strtolower(trim($mimeType));

        return $mimeType !== '' ? $mimeType : null;
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    /**
     * @return array<string, mixed>|null
     */
    private function file(string $key): ?array
    {
        $file = $this->request->file($key);

        return is_array($file) ? $file : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function uploadedFile(string $key): ?array
    {
        $file = $this->file($key);

        if ($file === null)
        {
            return null;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        return $error === UPLOAD_ERR_OK ? $file : null;
    }
}