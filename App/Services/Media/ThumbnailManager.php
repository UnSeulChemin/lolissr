<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;
use App\Services\UploadService;

use Framework\Config\UploadConfig;

final readonly class ThumbnailManager
{
    public function __construct(
        private UploadService $uploadService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     */
    public function upload(
        string $collection,
        string $name,
        int $numero,
        array $files
    ): ServiceResult|UploadThumbnailData {
        $result = $this->uploadService->uploadThumbnail(
            $name,
            $numero,
            UploadConfig::thumbnailDirectory($collection),
            $files
        );

        if (! $result->success)
        {
            return ServiceResult::error(
                message: $result->message,
                status: $result->status
            );
        }

        $upload = $result->data['upload'] ?? null;

        if (! $upload instanceof UploadThumbnailData)
        {
            return ServiceResult::error(
                message: 'Upload invalide'
            );
        }

        return $upload;
    }

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    public function rollback(
        UploadThumbnailData $upload
    ): bool {
        return $this->uploadService->removeFile(
            $upload->destinationPath
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION
    |--------------------------------------------------------------------------
    */

    public function remove(
        ?string $thumbnail,
        ?string $extension,
        string $collection
    ): bool {
        if (
            $thumbnail === null
            || $thumbnail === ''
            || $extension === null
            || $extension === ''
        )
        {
            return true;
        }

        $path =
            UploadConfig::thumbnailDirectory($collection)
            . $thumbnail
            . '.'
            . $extension;

        return $this->uploadService->removeFile($path);
    }
}