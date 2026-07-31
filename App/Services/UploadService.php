<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;
use App\Services\Media\ImageUploadValidator;
use App\Support\ThumbnailName;

use Framework\Application\App;
use Framework\Support\Logger;

final readonly class UploadService
{
    public function __construct(
        private ImageUploadValidator $imageUploadValidator
    ) {
    }

    // =========================================
    // UPLOAD
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function uploadThumbnail(
        string $name,
        int $numero,
        string $directory,
        array $files,
        string $fileKey = 'image'
    ): ServiceResult {
        if (App::isTesting())
        {
            return ServiceResult::error(
                message: 'Upload interdit pendant les tests HTTP',
                status: 403
            );
        }

        $validatedUpload = $this->imageUploadValidator->validate(
            $files,
            $fileKey
        );

        if ($validatedUpload instanceof ServiceResult)
        {
            return $validatedUpload;
        }

        $thumbnail = ThumbnailName::generate($name, $numero);

        if ($thumbnail === '')
        {
            return $this->failure(
                'Upload: nom thumbnail invalide.',
                'Nom de fichier invalide',
                422
            );
        }

        $destination = $this->buildDestinationPath(
            $directory,
            $thumbnail,
            $validatedUpload->extension
        );

        if ($destination === null)
        {
            return $this->failure(
                'Upload: dossier impossible à créer.',
                'Dossier image introuvable',
                500
            );
        }

        if (is_file($destination))
        {
            return $this->failure(
                'Upload: fichier déjà existant : ' . $destination,
                'Une image avec ce nom existe déjà',
                409
            );
        }

        if (
            ! @move_uploaded_file($validatedUpload->temporaryPath, $destination)
            || ! is_file($destination)
        ) {
            return $this->failure(
                'Upload: fichier non enregistré. tmp='
                . $validatedUpload->temporaryPath
                . ' destination='
                . $destination,
                'Image non enregistrée sur le disque',
                500
            );
        }

        return ServiceResult::success(
            message: 'Upload réussi',
            data: [
                'upload' => new UploadThumbnailData(
                    thumbnailPath: $thumbnail,
                    extension: $validatedUpload->extension,
                    destinationPath: $destination
                )
            ]
        );
    }

    // =========================================
    // SUPPRESSION
    // =========================================

    public function removeFile(string $path): bool
    {
        if (! is_file($path))
        {
            return true;
        }

        if (@unlink($path))
        {
            return true;
        }

        Logger::warning(
            'Upload: impossible de supprimer le fichier.',
            [
                'path' => $path
            ]
        );

        return false;
    }

    // =========================================
    // DESTINATION
    // =========================================

    private function buildDestinationPath(
        string $directory,
        string $thumbnail,
        string $extension
    ): ?string {
        $directory = rtrim(trim($directory), '/\\');

        if ($directory === '' || ! $this->ensureDirectoryExists($directory))
        {
            return null;
        }

        return $directory
            . DIRECTORY_SEPARATOR
            . $thumbnail
            . '.'
            . $extension;
    }

    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory))
        {
            return true;
        }

        return @mkdir($directory, 0755, true) || is_dir($directory);
    }

    // =========================================
    // RÉSULTAT
    // =========================================

    private function failure(
        string $logMessage,
        string $message,
        int $status
    ): ServiceResult {
        Logger::error($logMessage);

        return ServiceResult::error(
            message: $message,
            status: $status
        );
    }
}