<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;
use App\Support\ThumbnailName;

use Framework\Application\App;
use Framework\Config\UploadConfig;
use Framework\Support\Logger;

use finfo;

final readonly class UploadService
{
    private finfo $finfo;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
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
            return $this->failure('Upload interdit pendant les tests HTTP', 403);
        }

        $file = $this->uploadedFile($files, $fileKey);

        if ($file === null)
        {
            return $this->failUpload(
                'Upload: fichier introuvable.',
                'Fichier image introuvable',
                422
            );
        }

        $uploadError = $this->uploadError($file);

        if ($uploadError !== UPLOAD_ERR_OK)
        {
            return $this->failUpload(
                'Upload: erreur PHP détectée. Code=' . $uploadError,
                'Erreur pendant l’envoi de l’image',
                422
            );
        }

        $maxSize = UploadConfig::maxSize();
        $allowedExtensions = UploadConfig::allowedExtensions();
        $allowedMimeTypes = UploadConfig::allowedMimeTypes();

        $declaredSize = $this->fileSize($file);

        if ($declaredSize <= 0 || $declaredSize > $maxSize)
        {
            return $this->failUpload(
                'Upload: taille déclarée invalide. Taille=' . $declaredSize,
                'Taille de l’image invalide',
                422
            );
        }

        $extension = $this->fileExtension($file);

        if ($extension === null)
        {
            return $this->failUpload(
                'Upload: extension introuvable.',
                'Extension image introuvable',
                422
            );
        }

        if (! in_array($extension, $allowedExtensions, true))
        {
            return $this->failUpload(
                'Upload: extension non autorisée : ' . $extension,
                'Format image non autorisé',
                422
            );
        }

        $temporaryPath = $this->temporaryPath($file);

        if ($temporaryPath === null || ! is_uploaded_file($temporaryPath))
        {
            return $this->failUpload(
                'Upload: fichier temporaire invalide.',
                'Fichier temporaire introuvable',
                422
            );
        }

        $realSize = @filesize($temporaryPath);

        if (! is_int($realSize) || $realSize <= 0 || $realSize > $maxSize)
        {
            return $this->failUpload(
                'Upload: taille réelle invalide. Taille='
                . ($realSize === false ? 'false' : $realSize),
                'Taille réelle de l’image invalide',
                422
            );
        }

        $mimeType = $this->fileMimeType($temporaryPath);

        if ($mimeType === null || ! in_array($mimeType, $allowedMimeTypes, true))
        {
            return $this->failUpload(
                'Upload: MIME non autorisé. MIME reçu=' . ($mimeType ?? 'null'),
                'Type MIME image non autorisé',
                422
            );
        }

        $imageInfo = $this->imageInfo($temporaryPath);

        if ($imageInfo === null)
        {
            return $this->failUpload(
                'Upload: image impossible à décoder.',
                'Fichier image invalide',
                422
            );
        }

        if (! $this->hasValidImageDimensions($imageInfo['width'], $imageInfo['height']))
        {
            return $this->failUpload(
                sprintf(
                    'Upload: dimensions invalides. Largeur=%d Hauteur=%d',
                    $imageInfo['width'],
                    $imageInfo['height']
                ),
                'Dimensions de l’image non autorisées',
                422
            );
        }

        if (
            $imageInfo['mime'] !== $mimeType
            || ! in_array($imageInfo['mime'], $allowedMimeTypes, true)
        ) {
            return $this->failUpload(
                "Upload: incohérence MIME. finfo={$mimeType} image={$imageInfo['mime']}",
                'Type réel de l’image invalide',
                422
            );
        }

        $thumbnail = ThumbnailName::generate($name, $numero);

        if ($thumbnail === '')
        {
            return $this->failUpload(
                'Upload: nom thumbnail invalide.',
                'Nom de fichier invalide',
                422
            );
        }

        $destination = $this->buildDestinationPath(
            $directory,
            $thumbnail,
            $extension
        );

        if ($destination === null)
        {
            return $this->failUpload(
                'Upload: dossier impossible à créer.',
                'Dossier image introuvable',
                500
            );
        }

        if (is_file($destination))
        {
            return $this->failUpload(
                'Upload: fichier déjà existant : ' . $destination,
                'Une image avec ce nom existe déjà',
                409
            );
        }

        if (
            ! @move_uploaded_file($temporaryPath, $destination)
            || ! is_file($destination)
        ) {
            return $this->failUpload(
                "Upload: fichier non enregistré. tmp={$temporaryPath} destination={$destination}",
                'Image non enregistrée sur le disque',
                500
            );
        }

        return $this->uploadSuccess($thumbnail, $extension, $destination);
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
    // FICHIER
    // =========================================

    /**
     * @param array<string, mixed> $files
     *
     * @return array<string, mixed>|null
     */
    private function uploadedFile(array $files, string $fileKey): ?array
    {
        $file = $files[$fileKey] ?? null;

        return is_array($file) ? $file : null;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function uploadError(array $file): int
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        return is_int($error) ? $error : UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileSize(array $file): int
    {
        $size = $file['size'] ?? null;
        $validatedSize = filter_var($size, FILTER_VALIDATE_INT);

        return $validatedSize !== false && $validatedSize >= 0
            ? $validatedSize
            : 0;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileExtension(array $file): ?string
    {
        $name = $file['name'] ?? null;

        if (! is_string($name) || trim($name) === '')
        {
            return null;
        }

        $extension = strtolower(pathinfo(trim($name), PATHINFO_EXTENSION));

        if ($extension === '')
        {
            return null;
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function temporaryPath(array $file): ?string
    {
        $temporaryPath = $file['tmp_name'] ?? null;

        if (! is_string($temporaryPath))
        {
            return null;
        }

        $temporaryPath = trim($temporaryPath);

        return $temporaryPath !== '' ? $temporaryPath : null;
    }

    private function fileMimeType(string $temporaryPath): ?string
    {
        $mimeType = @$this->finfo->file($temporaryPath);

        if (! is_string($mimeType))
        {
            return null;
        }

        $mimeType = strtolower(trim($mimeType));

        return $mimeType !== '' ? $mimeType : null;
    }

    /**
     * @return array{
     *     width: int,
     *     height: int,
     *     mime: string
     * }|null
     */
    private function imageInfo(string $temporaryPath): ?array
    {
        $imageInfo = @getimagesize($temporaryPath);

        if ($imageInfo === false)
        {
            return null;
        }

        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;
        $mimeType = $imageInfo['mime'] ?? null;

        if (
            ! is_int($width)
            || ! is_int($height)
            || ! is_string($mimeType)
            || trim($mimeType) === ''
        ) {
            return null;
        }

        return [
            'width' => $width,
            'height' => $height,
            'mime' => strtolower(trim($mimeType))
        ];
    }

    private function hasValidImageDimensions(int $width, int $height): bool
    {
        if (
            $width <= 0
            || $height <= 0
            || $width > UploadConfig::maxWidth()
            || $height > UploadConfig::maxHeight()
        ) {
            return false;
        }

        return $width <= intdiv(UploadConfig::maxPixels(), $height);
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
    // RÉSULTATS
    // =========================================

    private function uploadSuccess(
        string $thumbnail,
        string $extension,
        string $destination
    ): ServiceResult {
        return ServiceResult::success(
            message: 'Upload réussi',
            data: [
                'upload' => new UploadThumbnailData(
                    thumbnailPath: $thumbnail,
                    extension: $extension,
                    destinationPath: $destination
                )
            ]
        );
    }

    private function failure(string $message, int $status): ServiceResult
    {
        return ServiceResult::error(
            message: $message,
            status: $status
        );
    }

    private function failUpload(
        string $logMessage,
        string $message,
        int $status
    ): ServiceResult {
        Logger::error($logMessage);

        return $this->failure($message, $status);
    }
}