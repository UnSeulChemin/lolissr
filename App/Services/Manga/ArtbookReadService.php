<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\ArtbookData;
use App\DTO\Manga\Responses\ArtbookListData;
use App\Models\Artbook;
use App\Repositories\Manga\ArtbookCollectionRepository;
use App\Repositories\Manga\ArtbookRepository;
use App\Repositories\Manga\ArtbookStatsRepository;

use Framework\Application\App;

final readonly class ArtbookReadService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private ArtbookCollectionRepository $collectionRepository,
        private ArtbookStatsRepository $statsRepository,
    ) {
    }

    public function artbooks(int|string $page = 1): ?ArtbookListData
    {
        $page = max(1, (int) $page);

        $perPage = App::pagination();

        $totalArtbooks = $this->statsRepository->countAll();

        if ($totalArtbooks === 0)
        {
            return null;
        }

        $totalPages = (int) ceil($totalArtbooks / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $artbooks = $this->collectionRepository->findPaginated(
            $perPage,
            $page,
        );

        return new ArtbookListData(
            artbooks: array_map($this->mapArtbook(...), $artbooks),
            compteur: $totalPages,
            currentPage: $page,
            totalArtbooks: $totalArtbooks,
            perPage: $perPage,
        );
    }

    public function one(string $slug, int $numero): ?ArtbookData
    {
        $artbook = $this->artbookRepository
            ->findOneBySlugAndNumero($slug, $numero);

        if ($artbook === null)
        {
            return null;
        }

        return $this->mapArtbook($artbook);
    }

    private function mapArtbook(
        Artbook $artbook
    ): ArtbookData
    {
        return new ArtbookData(
            id: $artbook->id,
            thumbnail: $artbook->thumbnail,
            extension: $artbook->extension,
            slug: $artbook->slug,
            numero: $artbook->numero,
            artbook: $artbook->artbook,
            auteur: $artbook->auteur,
            serie: $artbook->serie,
            createdAt: $artbook->created_at,
        );
    }
}