<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;

use Framework\Application\App;
use Framework\Config\UploadConfig;
use Framework\Support\Logger;
use Framework\Support\Str;

use finfo;

final readonly class UploadService
{
    private finfo $finfo;

    public function __construct()
    {
        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     */
    public function uploadThumbnail(
        string $name,
        int $number,
        string $directory,
        array $files,
        string $fileKey = 'image'
    ): ServiceResult {
        if (App::isTesting())
        {
            return $this->failure(
                'Upload interdit pendant les tests HTTP',
                403
            );
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

        if (! $this->hasSuccessfulUpload($file))
        {
            return $this->failUpload(
                'Upload: erreur PHP détectée. Code=' . $this->uploadError($file),
                'Erreur pendant l’envoi de l’image',
                422
            );
        }

        $size = $this->fileSize($file);

        if ($size <= 0 || $size > UploadConfig::maxSize())
        {
            return $this->failUpload(
                'Upload: taille invalide. Taille=' . $size,
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

        if (! in_array($extension, UploadConfig::allowedExtensions(), true))
        {
            return $this->failUpload(
                'Upload: extension non autorisée : ' . $extension,
                'Format image non autorisé',
                422
            );
        }

        $tmpName = $this->tmpName($file);

        if (! $this->isValidTmpFile($tmpName))
        {
            return $this->failUpload(
                'Upload: fichier temporaire invalide.',
                'Fichier temporaire introuvable',
                422
            );
        }

        assert($tmpName !== null);

        $realSize = filesize($tmpName);

        if (
            ! is_int($realSize)
            || $realSize <= 0
            || $realSize > UploadConfig::maxSize()
        )
        {
            return $this->failUpload(
                'Upload: taille réelle invalide. Taille='
                . ($realSize === false ? 'false' : $realSize),
                'Taille réelle de l’image invalide',
                422
            );
        }

        $mimeType = $this->fileMimeType($tmpName);

        if (
            $mimeType === null
            || ! in_array($mimeType, UploadConfig::allowedMimeTypes(), true)
        )
        {
            return $this->failUpload(
                'Upload: MIME non autorisé. MIME reçu=' . ($mimeType ?? 'null'),
                'Type MIME image non autorisé',
                422
            );
        }

        $imageInfo = $this->imageInfo($tmpName);

        if ($imageInfo === null)
        {
            return $this->failUpload(
                'Upload: image impossible à décoder.',
                'Fichier image invalide',
                422
            );
        }

        if (! $this->hasValidImageDimensions(
            $imageInfo['width'],
            $imageInfo['height']
        ))
        {
            return $this->failUpload(
                'Upload: dimensions invalides. Largeur='
                . $imageInfo['width']
                . ' Hauteur='
                . $imageInfo['height'],
                'Dimensions de l’image non autorisées',
                422
            );
        }

        if (
            ! in_array(
                $imageInfo['mime'],
                UploadConfig::allowedMimeTypes(),
                true
            )
            || $imageInfo['mime'] !== $mimeType
        )
        {
            return $this->failUpload(
                'Upload: incohérence MIME. finfo='
                . $mimeType
                . ' image='
                . $imageInfo['mime'],
                'Type réel de l’image invalide',
                422
            );
        }

        $thumbnail = Str::thumbnailName($name, $number);

        if ($thumbnail === '')
        {
            return $this->failUpload(
                'Upload: nom thumbnail invalide.',
                'Nom de fichier invalide',
                422
            );
        }

        $destination = $this->buildDestinationPath(
            $thumbnail,
            $extension,
            $directory
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
            ! move_uploaded_file($tmpName, $destination)
            || ! is_file($destination)
        )
        {
            return $this->failUpload(
                'Upload: fichier non enregistré. tmp='
                . $tmpName
                . ' destination='
                . $destination,
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

    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION
    |--------------------------------------------------------------------------
    */

    public function removeFile(string $path): bool
    {
        if (! is_file($path))
        {
            return true;
        }

        if (unlink($path))
        {
            return true;
        }

        Logger::warning(
            'Upload: impossible de supprimer le fichier : ' . $path
        );

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     *
     * @return array<string, mixed>|null
     */
    private function uploadedFile(
        array $files,
        string $fileKey
    ): ?array {
        $file = $files[$fileKey] ?? null;

        return is_array($file) ? $file : null;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function hasSuccessfulUpload(array $file): bool
    {
        return $this->uploadError($file) === UPLOAD_ERR_OK;
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
        $size = $file['size'] ?? 0;

        return is_int($size) || is_numeric($size)
            ? (int) $size
            : 0;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function originalFilename(array $file): ?string
    {
        $name = $file['name'] ?? null;

        if (! is_string($name) || trim($name) === '')
        {
            return null;
        }

        return trim($name);
    }

    /**
     * @param array<string, mixed> $file
     */
    private function fileExtension(array $file): ?string
    {
        $name = $this->originalFilename($file);

        if ($name === null)
        {
            return null;
        }

        $extension = strtolower(
            pathinfo($name, PATHINFO_EXTENSION)
        );

        if ($extension === '')
        {
            return null;
        }

        return $extension === 'jpeg'
            ? 'jpg'
            : $extension;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function tmpName(array $file): ?string
    {
        $tmpName = $file['tmp_name'] ?? null;

        if (! is_string($tmpName) || trim($tmpName) === '')
        {
            return null;
        }

        return $tmpName;
    }

    private function isValidTmpFile(?string $tmpName): bool
    {
        return is_string($tmpName)
            && is_uploaded_file($tmpName);
    }

    private function fileMimeType(string $tmpName): ?string
    {
        $mimeType = $this->finfo->file($tmpName);

        return is_string($mimeType)
            ? strtolower($mimeType)
            : null;
    }

    /**
     * @return array{
     *     width: int,
     *     height: int,
     *     mime: string
     * }|null
     */
    private function imageInfo(string $tmpName): ?array
    {
        $imageInfo = getimagesize($tmpName);

        if ($imageInfo === false)
        {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => strtolower($imageInfo['mime']),
        ];
    }

    private function hasValidImageDimensions(
        int $width,
        int $height
    ): bool {
        if (
            $width <= 0
            || $height <= 0
            || $width > UploadConfig::maxWidth()
            || $height > UploadConfig::maxHeight()
        )
        {
            return false;
        }

        return ($width * $height) <= UploadConfig::maxPixels();
    }

    private function ensureDirectoryExists(string $directory): bool
    {
        if (is_dir($directory))
        {
            return true;
        }

        return mkdir($directory, 0755, true)
            || is_dir($directory);
    }

    private function buildDestinationPath(
        string $thumbnail,
        string $extension,
        string $directory
    ): ?string {
        if (! $this->ensureDirectoryExists($directory))
        {
            return null;
        }

        return $directory
            . $thumbnail
            . '.'
            . $extension;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function failure(
        string $message,
        int $status
    ): ServiceResult {
        return ServiceResult::error(
            message: $message,
            status: $status
        );
    }

    private function success(
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
                ),
            ]
        );
    }

    private function failUpload(
        string $logMessage,
        string $message,
        int $status
    ): ServiceResult {
        Logger::error($logMessage);

        return $this->failure(
            $message,
            $status
        );
    }
}