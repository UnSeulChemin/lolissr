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

        $tmp = trim((string) ($file['tmp_name'] ?? ''));

        return $tmp !== '' ? $tmp : null;
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

        return (int) ($file['size'] ?? 0);
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
        $file = $this->uploadedFile($key);

        if ($file === null)
        {
            return null;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($tmp === '' || ! is_file($tmp))
        {
            return null;
        }

        $mimeType = $this->finfo->file($tmp);

        return is_string($mimeType) && $mimeType !== ''
            ? strtolower($mimeType)
            : null;
    }

    // =========================================
    // UTILITAIRES
    // =========================================

    /**
     * @return array<string, mixed>|null
     */
    private function file(string $key): ?array
    {
        return $this->request->files()[$key] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function uploadedFile(string $key): ?array
    {
        $file = $this->file($key);

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
        {
            return null;
        }

        return $file;
    }
}
