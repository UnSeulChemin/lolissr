<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\DTO\Common\ServiceResult;
use App\DTO\Upload\UploadThumbnailData;
use App\Services\UploadService;
use App\Support\ThumbnailDirectory;

final readonly class ThumbnailManager
{
    public function __construct(private UploadService $uploadService)
    {
    }

    // =========================================
    // UPLOAD
    // =========================================

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
            ThumbnailDirectory::resolve($collection),
            $files
        );

        if (! $result->success)
        {
            return ServiceResult::error(
                message: $result->message,
                status: $result->status,
                data: $result->data
            );
        }

        $upload = $result->data['upload'] ?? null;

        if (! $upload instanceof UploadThumbnailData)
        {
            return ServiceResult::error(
                message: 'Données d’upload invalides',
                status: 500
            );
        }

        return $upload;
    }

    // =========================================
    // ROLLBACK
    // =========================================

    public function rollback(UploadThumbnailData $upload): bool
    {
        return $this->uploadService->removeFile($upload->destinationPath);
    }

    // =========================================
    // SUPPRESSION
    // =========================================

    public function remove(
        ?string $thumbnail,
        ?string $extension,
        string $collection
    ): bool {
        $thumbnail = $thumbnail !== null ? trim($thumbnail) : '';
        $extension = $extension !== null ? trim($extension) : '';

        if ($thumbnail === '' || $extension === '')
        {
            return true;
        }

        $path = ThumbnailDirectory::resolve($collection)
            . $thumbnail
            . '.'
            . $extension;

        return $this->uploadService->removeFile($path);
    }
}