<?php

declare(strict_types=1);

namespace App\Services\Collections\Concerns;

use App\Support\Media\ImageAssets;

use Framework\Config\ApplicationConfig;

trait BuildsCollectionReadData
{
    /**
     * @template T of object
     * @template U of object
     * @param callable(int, int): list<T> $load
     * @param callable(T): U $map
     * @return array{items: list<U>, currentPage: int, totalItems: int, perPage: int, totalPages: int}|null
     */
    private function collectionPage(int|string $page, int $total, callable $load, callable $map): ?array
    {
        $page = max(1, (int) $page);
        $perPage = ApplicationConfig::pagination();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) return null;

        return [
            'items' => $total === 0 ? [] : array_map($map, $load($perPage, $page)),
            'currentPage' => $page,
            'totalItems' => $total,
            'perPage' => $perPage,
            'totalPages' => $totalPages
        ];
    }

    /** @return array{thumbnail: ?string, extension: ?string, thumbnailUrl: ?string} */
    private function collectionThumbnail(string $category, string $thumbnail, string $extension, bool $grid = false): array
    {
        $thumbnail = $thumbnail !== '' ? $thumbnail : null;
        $extension = $extension !== '' ? $extension : null;
        $baseUri = $grid ? view_base_uri() : ApplicationConfig::baseUri();
        $url = $thumbnail !== null && $extension !== null
            ? $baseUri . "images/{$category}/thumbnail/{$thumbnail}.{$extension}"
            : null;

        return [
            'thumbnail' => $thumbnail,
            'extension' => $extension,
            'thumbnailUrl' => $url !== null && $grid ? ImageAssets::url($url, true) : $url
        ];
    }
}
