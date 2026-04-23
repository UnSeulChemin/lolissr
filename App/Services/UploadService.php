<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\Core\Support\Str;

class UploadService
{
    /**
     * Retourne true si le mode upload de test est actif.
     */
    public function isTestUploadMode(): bool
    {
        return App::isTesting();
    }

    /**
     * Retourne le dossier d’upload de test absolu.
     */
    public function testUploadDirectory(): string
    {
        $directory = trim(
            (string) env('TEST_UPLOAD_DIR', 'tests/Http/tmp-uploads'),
            '/\\'
        );

        return app_path($directory) . '/';
    }

    /**
     * Retourne le dossier d’upload cible.
     */
    private function uploadDirectory(): string
    {
        if ($this->isTestUploadMode())
        {
            return $this->testUploadDirectory();
        }

        return UploadConfig::mangaThumbnailDirectory();
    }

    /**
     * Retourne les infos d’un fichier uploadé.
     *
     * @return array<string, mixed>|null
     */
    private function fileData(array $files, string $fileKey): ?array
    {
        $file = $files[$fileKey] ?? null;

        if (!is_array($file))
        {
            return null;
        }

        return $file;
    }

    /**
     * Retourne le nom original du fichier.
     */
    private function originalFilename(array $file): ?string
    {
        $name = $file['name'] ?? null;

        if (!is_string($name) || trim($name) === '')
        {
            return null;
        }

        return $name;
    }

    /**
     * Retourne l’extension normalisée du fichier.
     */
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

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        return $extension;
    }

    /**
     * Retourne le chemin temporaire du fichier.
     */
    private function tmpName(array $file): ?string
    {
        $tmpName = $file['tmp_name'] ?? null;

        if (!is_string($tmpName) || trim($tmpName) === '')
        {
            return null;
        }

        return $tmpName;
    }

    /**
     * Retourne le type MIME réel du fichier temporaire.
     */
    private function fileMimeType(array $file): ?string
    {
        $tmpName = $this->tmpName($file);

        if ($tmpName === null || !is_file($tmpName))
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

        if (!is_string($mimeType) || $mimeType === '')
        {
            return null;
        }

        return strtolower($mimeType);
    }

    /**
     * Vérifie que le fichier temporaire est valide.
     */
    private function isValidTmpFile(?string $tmpName): bool
    {
        if ($tmpName === null)
        {
            return false;
        }

        if ($this->isTestUploadMode())
        {
            return is_file($tmpName);
        }

        return is_uploaded_file($tmpName);
    }

    /**
     * S'assure que le dossier existe.
     */
    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory))
        {
            return true;
        }

        if (mkdir($directory, 0777, true))
        {
            return true;
        }

        return is_dir($directory);
    }

    /**
     * Upload la miniature manga.
     *
     * @return array{
     *     success: bool,
     *     message?: string,
     *     status?: int,
     *     thumbnail?: string,
     *     extension?: string,
     *     destination?: string
     * }
     */
    public function uploadThumbnail(
        string $livre,
        int $numero,
        array $files,
        string $fileKey = 'image'
    ): array {
        $file = $this->fileData($files, $fileKey);

        if ($file === null)
        {
            Logger::error('Upload manga: fichier introuvable dans les données upload.');

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

        $mimeType = $this->fileMimeType($file);

        if (
            $mimeType === null
            || !in_array($mimeType, UploadConfig::allowedMimeTypes(), true)
        )
        {
            Logger::error(
                'Upload manga refusé: type MIME invalide. MIME reçu: '
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
            Logger::error('Upload manga: fichier temporaire invalide ou absent.');

            return [
                'success' => false,
                'message' => 'Fichier temporaire introuvable',
                'status' => 422
            ];
        }

        $thumbnail = Str::thumbnailName($livre, $numero);

        if ($thumbnail === '')
        {
            Logger::error('Upload manga: nom de thumbnail invalide.');

            return [
                'success' => false,
                'message' => 'Nom de fichier invalide',
                'status' => 422
            ];
        }

        $directory = $this->uploadDirectory();

        if (!$this->ensureDirectoryExists($directory))
        {
            Logger::error(
                'Upload manga: impossible de créer le dossier image : '
                . $directory
            );

            return [
                'success' => false,
                'message' => 'Dossier image introuvable',
                'status' => 500
            ];
        }

        $destination = $directory . $thumbnail . '.' . $extension;

        if (file_exists($destination))
        {
            Logger::error('Upload manga: fichier déjà existant : ' . $destination);

            return [
                'success' => false,
                'message' => 'Une image avec ce nom existe déjà',
                'status' => 409
            ];
        }

        $moved = $this->isTestUploadMode()
            ? rename($tmpName, $destination)
            : move_uploaded_file($tmpName, $destination);

        if (!$moved)
        {
            Logger::error(
                'Upload manga: échec déplacement fichier vers : '
                . $destination
            );

            return [
                'success' => false,
                'message' => 'Erreur lors de l’upload de l’image',
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

    /**
     * Supprime un fichier si présent.
     */
    public function removeFileIfExists(string $path): void
    {
        if (is_file($path))
        {
            unlink($path);
        }
    }
}