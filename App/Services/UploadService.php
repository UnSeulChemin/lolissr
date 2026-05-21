<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Upload\UploadThumbnailData;
use App\DTO\Upload\UploadThumbnailResultData;
use finfo;
use Framework\Application\App;
use Framework\Config\UploadConfig;
use Framework\Support\Logger;
use Framework\Support\Str;

final readonly class UploadService
{
    public function isTestUploadMode(): bool
    {
        return App::isTesting()
            && env_bool(
                'TEST_UPLOAD_MODE',
                false,
            );
    }

    public function testUploadDirectory(): string
    {
        $directory = trim(
            (string) env(
                'TEST_UPLOAD_DIR',
                'tests/Http/tmp-uploads',
            ),
            '/\\',
        );

        return rtrim(
            base_path($directory),
            '/\\',
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
    private function uploadedFile(
        array $files,
        string $fileKey,
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
        array $file,
    ): ?string {
        $name = $file['name'] ?? null;

        if (
            !is_string($name)
            || trim($name) === ''
        ) {
            return null;
        }

        return trim($name);
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileExtension(
        array $file,
    ): ?string {
        $name = $this->originalFilename($file);

        if ($name === null) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $name,
                PATHINFO_EXTENSION,
            ),
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
        array $file,
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

    private function isValidTmpFile(
        ?string $tmpName,
    ): bool {
        if ($tmpName === null) {
            return false;
        }

        return $this->isTestUploadMode()
            ? is_file($tmpName)
            : is_uploaded_file($tmpName);
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileMimeType(
        array $file,
    ): ?string {
        $tmpName = $this->tmpName($file);

        if (
            !$this->isValidTmpFile($tmpName)
        ) {
            return null;
        }

        $finfo = new finfo(
            FILEINFO_MIME_TYPE,
        );

        $mimeType = $finfo->file(
            $tmpName,
        );

        return is_string($mimeType)
            ? strtolower($mimeType)
            : null;
    }

    private function ensureDirectoryExists(
        string $directory,
    ): bool {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir(
            $directory,
            0755,
            true,
        ) || is_dir($directory);
    }

    public function removeFile(
        string $path,
    ): void {
        if (!is_file($path)) {
            return;
        }

        if (!unlink($path)) {
            Logger::error(
                'Impossible de supprimer le fichier : '
                . $path,
            );
        }
    }

    private function failure(
        string $message,
        int $status,
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
        string $destination,
    ): UploadThumbnailResultData {
        return new UploadThumbnailResultData(
            success: true,
            message: 'Upload réussi',
            status: 200,
            data: new UploadThumbnailData(
                thumbnail: $thumbnail,
                extension: $extension,
                destination: $destination,
            ),
        );
    }

    private function failUpload(
        string $logMessage,
        string $message,
        int $status,
    ): UploadThumbnailResultData {
        Logger::error($logMessage);

        return $this->failure(
            $message,
            $status,
        );
    }

    private function buildDestinationPath(
        string $thumbnail,
        string $extension,
    ): ?string {
        $directory = $this->uploadDirectory();

        if (
            !$this->ensureDirectoryExists(
                $directory,
            )
        ) {
            return null;
        }

        return $directory
            . $thumbnail
            . '.'
            . $extension;
    }

    private function moveFile(
        string $tmpName,
        string $destination,
    ): bool {
        return $this->isTestUploadMode()
            ? copy(
                $tmpName,
                $destination,
            )
            : move_uploaded_file(
                $tmpName,
                $destination,
            );
    }

    /**
     * @param array<string, mixed> $files
     */
    public function uploadThumbnail(
        string $livre,
        int $numero,
        array $files,
        string $fileKey = 'image',
    ): UploadThumbnailResultData {
        $file = $this->uploadedFile(
            $files,
            $fileKey,
        );

        if ($file === null) {
            return $this->failUpload(
                'Upload manga: fichier introuvable.',
                'Fichier image introuvable',
                422,
            );
        }

        $extension = $this->fileExtension(
            $file,
        );

        if ($extension === null) {
            return $this->failUpload(
                'Upload manga: extension introuvable.',
                'Extension image introuvable',
                422,
            );
        }

        if (
            !in_array(
                $extension,
                UploadConfig::allowedExtensions(),
                true,
            )
        ) {
            return $this->failUpload(
                'Upload manga: extension non autorisée : '
                . $extension,
                'Format image non autorisé',
                422,
            );
        }

        $mimeType = $this->fileMimeType(
            $file,
        );

        if (
            $mimeType === null
            || !in_array(
                $mimeType,
                UploadConfig::allowedMimeTypes(),
                true,
            )
        ) {
            return $this->failUpload(
                'Upload manga: MIME non autorisé. MIME reçu: '
                . ($mimeType ?? 'null'),
                'Type MIME image non autorisé',
                422,
            );
        }

        $tmpName = $this->tmpName($file);

        if (
            !$this->isValidTmpFile(
                $tmpName,
            )
        ) {
            return $this->failUpload(
                'Upload manga: fichier temporaire invalide.',
                'Fichier temporaire introuvable',
                422,
            );
        }

        $thumbnail = Str::thumbnailName(
            $livre,
            $numero,
        );

        if ($thumbnail === '') {
            return $this->failUpload(
                'Upload manga: nom thumbnail invalide.',
                'Nom de fichier invalide',
                422,
            );
        }

        $destination =
            $this->buildDestinationPath(
                $thumbnail,
                $extension,
            );

        if ($destination === null) {
            return $this->failUpload(
                'Upload manga: dossier impossible à créer.',
                'Dossier image introuvable',
                500,
            );
        }

        if (is_file($destination)) {
            if ($this->isTestUploadMode()) {
                $this->removeFile(
                    $destination,
                );
            } else {
                return $this->failUpload(
                    'Upload manga: fichier déjà existant : '
                    . $destination,
                    'Une image avec ce nom existe déjà',
                    409,
                );
            }
        }

        if (
            !$this->moveFile(
                $tmpName,
                $destination,
            )
            || !is_file($destination)
        ) {
            return $this->failUpload(
                'Upload manga: fichier non enregistré. tmp='
                . $tmpName
                . ' destination='
                . $destination,
                'Image non enregistrée sur le disque',
                500,
            );
        }

        return $this->success(
            $thumbnail,
            $extension,
            $destination,
        );
    }
}