<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\Core\Support\Str;
use App\DTO\Upload\UploadThumbnailData;
use App\DTO\Upload\UploadThumbnailResultData;

final class UploadService
{
    public function isTestUploadMode(): bool
    {
        return App::isTesting()
            && env_bool('TEST_UPLOAD_MODE', false);
    }

    public function testUploadDirectory(): string
    {
        $directory = trim(
            (string) env(
                'TEST_UPLOAD_DIR',
                'tests/Http/tmp-uploads'
            ),
            '/\\'
        );

        return rtrim(
            app_path($directory),
            '/\\'
        ) . DIRECTORY_SEPARATOR;
    }

    private function uploadDirectory(): string
    {
        return $this->isTestUploadMode()
            ? $this->testUploadDirectory()
            : UploadConfig::mangaThumbnailDirectory();
    }

    /**
     * @param array<string, mixed> $files
     * @return array<string, mixed>|null
     */
    private function fileData(
        array $files,
        string $fileKey
    ): ?array {
        $file = $files[$fileKey] ?? null;

        return is_array($file)
            ? $file
            : null;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function originalFilename(
        array $file
    ): ?string {
        $name = $file['name'] ?? null;

        if (
            !is_string($name)
            || trim($name) === ''
        ) {
            return null;
        }

        return $name;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileExtension(
        array $file
    ): ?string {
        $name = $this->originalFilename($file);

        if ($name === null) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $name,
                PATHINFO_EXTENSION
            )
        );

        if ($extension === '') {
            return null;
        }

        return $extension === 'jpeg'
            ? 'jpg'
            : $extension;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function tmpName(
        array $file
    ): ?string {
        $tmpName = $file['tmp_name'] ?? null;

        if (
            !is_string($tmpName)
            || trim($tmpName) === ''
        ) {
            return null;
        }

        return $tmpName;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileMimeType(
        array $file
    ): ?string {
        $tmpName = $this->tmpName($file);

        if ($tmpName === null) {
            return null;
        }

        if (
            !$this->isTestUploadMode()
            && !is_uploaded_file($tmpName)
        ) {
            return null;
        }

        if (!is_file($tmpName)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file(
            $finfo,
            $tmpName
        );

        finfo_close($finfo);

        return is_string($mimeType)
            ? strtolower($mimeType)
            : null;
    }

    private function isValidTmpFile(
        ?string $tmpName
    ): bool {
        if ($tmpName === null) {
            return false;
        }

        return $this->isTestUploadMode()
            ? is_file($tmpName)
            : is_uploaded_file($tmpName);
    }

    private function ensureDirectoryExists(
        string $directory
    ): bool {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir(
            $directory,
            0777,
            true
        ) || is_dir($directory);
    }

    private function failure(
        string $message,
        int $status
    ): UploadThumbnailResultData {
        return new UploadThumbnailResultData(
            success: false,
            message: $message,
            status: $status,
        );
    }

    private function success(
        string $thumbnail,
        string $extension,
        string $destination
    ): UploadThumbnailResultData {
        return new UploadThumbnailResultData(
            success: true,
            message: 'Upload réussi',
            status: 200,
            data: new UploadThumbnailData(
                thumbnail: $thumbnail,
                extension: $extension,
                destination: $destination,
            )
        );
    }

    /**
     * @param array<string, mixed> $files
     */
    public function uploadThumbnail(
        string $livre,
        int $numero,
        array $files,
        string $fileKey = 'image'
    ): UploadThumbnailResultData {
        $file = $this->fileData(
            $files,
            $fileKey
        );

        if ($file === null) {
            Logger::error(
                'Upload manga: fichier introuvable.'
            );

            return $this->failure(
                'Fichier image introuvable',
                422
            );
        }

        $extension = $this->fileExtension($file);

        if ($extension === null) {
            Logger::error(
                'Upload manga: extension introuvable.'
            );

            return $this->failure(
                'Extension image introuvable',
                422
            );
        }

        if (
            !in_array(
                $extension,
                UploadConfig::allowedExtensions(),
                true
            )
        ) {
            Logger::error(
                'Upload manga: extension non autorisée : '
                . $extension
            );

            return $this->failure(
                'Format image non autorisé',
                422
            );
        }

        $mimeType = $this->fileMimeType($file);

        if (
            $mimeType === null
            || !in_array(
                $mimeType,
                UploadConfig::allowedMimeTypes(),
                true
            )
        ) {
            Logger::error(
                'Upload manga: MIME non autorisé. MIME reçu: '
                . ($mimeType ?? 'null')
            );

            return $this->failure(
                'Type MIME image non autorisé',
                422
            );
        }

        $tmpName = $this->tmpName($file);

        if (!$this->isValidTmpFile($tmpName)) {
            Logger::error(
                'Upload manga: fichier temporaire invalide.'
            );

            return $this->failure(
                'Fichier temporaire introuvable',
                422
            );
        }

        $thumbnail = Str::thumbnailName(
            $livre,
            $numero
        );

        if ($thumbnail === '') {
            Logger::error(
                'Upload manga: nom thumbnail invalide.'
            );

            return $this->failure(
                'Nom de fichier invalide',
                422
            );
        }

        $directory = $this->uploadDirectory();

        if (
            !$this->ensureDirectoryExists($directory)
        ) {
            Logger::error(
                'Upload manga: dossier impossible à créer : '
                . $directory
            );

            return $this->failure(
                'Dossier image introuvable',
                500
            );
        }

        $destination = $directory
            . $thumbnail
            . '.'
            . $extension;

        if (is_file($destination)) {
            if ($this->isTestUploadMode()) {
                unlink($destination);
            } else {
                Logger::error(
                    'Upload manga: fichier déjà existant : '
                    . $destination
                );

                return $this->failure(
                    'Une image avec ce nom existe déjà',
                    409
                );
            }
        }

        $moved = $this->isTestUploadMode()
            ? copy(
                $tmpName,
                $destination
            )
            : move_uploaded_file(
                $tmpName,
                $destination
            );

        if (
            !$moved
            || !is_file($destination)
        ) {
            Logger::error(
                'Upload manga: fichier non enregistré. tmp='
                . $tmpName
                . ' destination='
                . $destination
            );

            return $this->failure(
                'Image non enregistrée sur le disque',
                500
            );
        }

        return $this->success(
            $thumbnail,
            $extension,
            $destination
        );
    }

    public function removeFileIfExists(
        string $path
    ): void {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
