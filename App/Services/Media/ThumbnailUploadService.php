<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\DTO\Common\ServiceResult;
use App\DTO\Media\UploadThumbnailData;
use App\Support\Media\ThumbnailDirectory;

final readonly class ThumbnailUploadService
{
    public function __construct(private UploadService $uploadService)
    {
    }

    // =================================================
    // TÉLÉVERSEMENT
    // =================================================

    /**
     * @param array<string, mixed> $files
     */
    public function upload(string $collection, string $name, int $numero, array $files): ServiceResult|UploadThumbnailData
    {
        $result = $this->uploadService->uploadThumbnail(
            $name,
            $numero,
            ThumbnailDirectory::resolve($collection),
            $files
        );

        if (! $result->success)
        {
            return ServiceResult::error(message: $result->message, status: $result->status, data: $result->data);
        }

        $upload = $result->data['upload'] ?? null;

        if (! $upload instanceof UploadThumbnailData)
        {
            return ServiceResult::error(message: 'Données d’upload invalides', status: 500);
        }

        return $upload;
    }

    // =================================================
    // ANNULATION
    // =================================================

    public function rollback(UploadThumbnailData $upload): bool
    {
        return $this->uploadService->removeFile($upload->destinationPath);
    }

    // =================================================
    // SUPPRESSION
    // =================================================

    public function remove(?string $thumbnail, ?string $extension, string $collection): bool
    {
        if (preg_match('/[\x00-\x1f\x7f]/', $thumbnail ?? '') === 1
            || preg_match('/[\x00-\x1f\x7f]/', $extension ?? '') === 1) return false;
        $thumbnail = $thumbnail !== null ? trim($thumbnail) : '';
        $extension = $extension !== null ? trim($extension) : '';

        if ($thumbnail === '' || $extension === '')
        {
            return true;
        }

        // Keep historical Unicode/space-containing names, but reject paths and Windows alternate streams.
        if ($thumbnail === '.' || $thumbnail === '..'
            || preg_match('/[\\\\\/:\x00-\x1f\x7f]/', $thumbnail) === 1
            || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'], true))
        {
            return false;
        }

        $directory = ThumbnailDirectory::resolve($collection);
        $resolvedDirectory = realpath($directory);
        if ($resolvedDirectory === false) return !file_exists($directory) && !is_link(rtrim($directory, '/\\'));
        $path = $directory . $thumbnail . '.' . $extension;
        $grid = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.grid.$1', $path);
        if (!$this->isConfined($path, $resolvedDirectory)
            || (is_string($grid) && $grid !== $path && !$this->isConfined($grid, $resolvedDirectory)))
        {
            return false;
        }

        return $this->uploadService->removeFile($path);
    }

    private function isConfined(string $path, string $directory): bool
    {
        // Reject symlinks, including dangling ones, before cleanup touches image metadata or grid files.
        if (is_link($path)) return false;
        $resolved = realpath($path);
        if ($resolved === false) return !file_exists($path);
        if (!is_file($resolved)) return false;
        $parent = dirname($resolved);
        return PHP_OS_FAMILY === 'Windows'
            ? strcasecmp($parent, $directory) === 0
            : $parent === $directory;
    }
}
