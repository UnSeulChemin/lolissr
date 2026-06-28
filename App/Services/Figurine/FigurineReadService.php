<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\DTO\Figurine\Responses\FigurineData;
use App\Repositories\Figurine\FigurineRepository;

final readonly class FigurineReadService
{
    public function __construct(
        private FigurineRepository $figurineRepository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | WAIFUS
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<FigurineData>
     */
    public function waifus(): array
    {
        return $this->figurineRepository->findAllDto();
    }

    public function one(string $slug): ?FigurineData
    {
        return $this->figurineRepository->findDtoBySlug($slug);
    }
}