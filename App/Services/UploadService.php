<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\Core\Support\Str;

class UploadService
{
    public function isTestUploadMode(): bool
    {
        return App::isTesting();
    }

    public function testUploadDirectory(): string
    {
        $directory = trim(
            (string) env('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'),
            '/\\'
        );

        return rtrim(app_path($directory), '/\\') . DIRECTORY_SEPARATOR;
    }

    private function uploadDirectory(): string
    {
        return $this->isTestUploadMode()
            ? $this->testUploadDirectory()
            : UploadConfig::mangaThumbnailDirectory();
    }

    private function fileData(array $files, string $fileKey): ?array
    {
        $file = $files[$fileKey] ?? null;

        return is_array($file) ? $file : null;
    }

    private function originalFilename(array $file): ?string
    {
        $name = $file['name'] ?? null;

        if (!is_string($name) || trim($name) === '')
        {
            return null;
        }

        return $name;
    }

    private function fileExtension(array $file): ?string
    {
        $name = $this->originalFilename($file);

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

    private function tmpName(array $file): ?string
    {
        $tmpName = $file['tmp_name'] ?? null;

        if (!is_string($tmpName) || trim($tmpName) === '')
        {
            return null;
        }

        return $tmpName;
    }

    private function fileMimeType(array $file): ?string
    {
        $tmpName = $this->tmpName($file);

        if ($tmpName === null)
        {
            return null;
        }

        if (
            !$this->isTestUploadMode()
            && !is_uploaded_file($tmpName)
        ) {
            return null;
        }

        if (!is_file($tmpName))
        {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false)
        {
            return null;
        }

        $mimeType = finfo_file($finfo, $tmpName);

        finfo_close($finfo);

        return is_string($mimeType)
            ? strtolower($mimeType)
            : null;
    }

    private function isValidTmpFile(?string $tmpName): bool
    {
        if ($tmpName === null)
        {
            return false;
        }

        return $this->isTestUploadMode()
            ? is_file($tmpName)
            : is_uploaded_file($tmpName);
    }

    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory))
        {
            return true;
        }

        if (!mkdir($directory, 0777, true) && !is_dir($directory))
        {
            return false;
        }

        return true;
    }

    public function uploadThumbnail(
        string $livre,
        int $numero,
        array $files,
        string $fileKey = 'image'
    ): array {
        $file = $this->fileData($files, $fileKey);

        if ($file === null)
        {
            Logger::error('Upload manga: fichier introuvable.');

            return [
                'success' => false,
                'message' => 'Fichier image introuvable',
                'status' => 422
            ];
        }

        $extension = $this->fileExtension($file);

        if ($extension === null)
        {
            Logger::error('Upload manga: extension introuvable.');

            return [
                'success' => false,
                'message' => 'Extension image introuvable',
                'status' => 422
            ];
        }

        if (!in_array($extension, UploadConfig::allowedExtensions(), true))
        {
            Logger::error('Upload manga: extension non autorisée : ' . $extension);

            return [
                'success' => false,
                'message' => 'Format image non autorisé',
                'status' => 422
            ];
        }

        $mimeType = $this->fileMimeType($file);

        if (
            $mimeType === null
            || !in_array($mimeType, UploadConfig::allowedMimeTypes(), true)
        )
        {
            Logger::error(
                'Upload manga: MIME non autorisé. MIME reçu: '
                . ($mimeType ?? 'null')
            );

            return [
                'success' => false,
                'message' => 'Type MIME image non autorisé',
                'status' => 422
            ];
        }

        $tmpName = $this->tmpName($file);

        if (!$this->isValidTmpFile($tmpName))
        {
            Logger::error('Upload manga: fichier temporaire invalide.');

            return [
                'success' => false,
                'message' => 'Fichier temporaire introuvable',
                'status' => 422
            ];
        }

        $thumbnail = Str::thumbnailName($livre, $numero);

        if ($thumbnail === '')
        {
            Logger::error('Upload manga: nom thumbnail invalide.');

            return [
                'success' => false,
                'message' => 'Nom de fichier invalide',
                'status' => 422
            ];
        }

        $directory = $this->uploadDirectory();

        if (!$this->ensureDirectoryExists($directory))
        {
            Logger::error('Upload manga: dossier impossible à créer : ' . $directory);

            return [
                'success' => false,
                'message' => 'Dossier image introuvable',
                'status' => 500
            ];
        }

        $destination = $directory . $thumbnail . '.' . $extension;

        if (is_file($destination))
        {
            if ($this->isTestUploadMode()) {
                unlink($destination);
            } else {
                Logger::error(
                    'Upload manga: fichier déjà existant : ' . $destination
                );

                return [
                    'success' => false,
                    'message' => 'Une image avec ce nom existe déjà',
                    'status' => 409
                ];
            }
        }

        $moved = $this->isTestUploadMode()
            ? copy((string) $tmpName, $destination)
            : move_uploaded_file((string) $tmpName, $destination);

        if (!$moved || !is_file($destination))
        {
            Logger::error(
                'Upload manga: fichier non enregistré. tmp='
                . (string) $tmpName
                . ' destination='
                . $destination
            );

            return [
                'success' => false,
                'message' => 'Image non enregistrée sur le disque',
                'status' => 500
            ];
        }

        return [
            'success' => true,
            'thumbnail' => $thumbnail,
            'extension' => $extension,
            'destination' => $destination
        ];
    }

    public function removeFileIfExists(string $path): void
    {
        if (is_file($path))
        {
            unlink($path);
        }
    }
}