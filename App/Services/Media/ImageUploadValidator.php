<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\ValidatedImageUploadData;

use Framework\Config\UploadConfig;
use Framework\Support\Logger;

use finfo;

final readonly class ImageUploadValidator
{
    private finfo $finfo;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
    }

    // =========================================
    // VALIDATION
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function validate(
        array $files,
        string $fileKey = 'image'
    ): ServiceResult|ValidatedImageUploadData {
        $file = $this->uploadedFile($files, $fileKey);

        if ($file === null)
        {
            return $this->failure(
                'Upload: fichier introuvable.',
                'Fichier image introuvable',
                422
            );
        }

        $uploadError = $this->uploadError($file);

        if ($uploadError !== UPLOAD_ERR_OK)
        {
            return $this->failure(
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
            return $this->failure(
                'Upload: taille déclarée invalide. Taille=' . $declaredSize,
                'Taille de l’image invalide',
                422
            );
        }

        $extension = $this->fileExtension($file);

        if ($extension === null)
        {
            return $this->failure(
                'Upload: extension introuvable.',
                'Extension image introuvable',
                422
            );
        }

        if (! in_array($extension, $allowedExtensions, true))
        {
            return $this->failure(
                'Upload: extension non autorisée : ' . $extension,
                'Format image non autorisé',
                422
            );
        }

        $temporaryPath = $this->temporaryPath($file);

        if ($temporaryPath === null || ! is_uploaded_file($temporaryPath))
        {
            return $this->failure(
                'Upload: fichier temporaire invalide.',
                'Fichier temporaire introuvable',
                422
            );
        }

        $realSize = @filesize($temporaryPath);

        if (! is_int($realSize) || $realSize <= 0 || $realSize > $maxSize)
        {
            return $this->failure(
                'Upload: taille réelle invalide. Taille='
                . ($realSize === false ? 'false' : $realSize),
                'Taille réelle de l’image invalide',
                422
            );
        }

        $mimeType = $this->fileMimeType($temporaryPath);

        if ($mimeType === null || ! in_array($mimeType, $allowedMimeTypes, true))
        {
            return $this->failure(
                'Upload: MIME non autorisé. MIME reçu=' . ($mimeType ?? 'null'),
                'Type MIME image non autorisé',
                422
            );
        }

        $imageInfo = $this->imageInfo($temporaryPath);

        if ($imageInfo === null)
        {
            return $this->failure(
                'Upload: image impossible à décoder.',
                'Fichier image invalide',
                422
            );
        }

        if (! $this->hasValidImageDimensions($imageInfo['width'], $imageInfo['height']))
        {
            return $this->failure(
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
            return $this->failure(
                "Upload: incohérence MIME. finfo={$mimeType} image={$imageInfo['mime']}",
                'Type réel de l’image invalide',
                422
            );
        }

        return new ValidatedImageUploadData(
            temporaryPath: $temporaryPath,
            extension: $extension
        );
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

        if (! is_string($name))
        {
            return null;
        }

        $name = trim($name);

        if ($name === '')
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

        $mimeType = strtolower(trim($imageInfo['mime']));

        if ($mimeType === '')
        {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $mimeType
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