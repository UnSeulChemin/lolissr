<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MangaRepository;

final class StatsService
{
    public function __construct(
        private readonly MangaRepository $repository = new MangaRepository()
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | GLOBAL STATS
    |--------------------------------------------------------------------------
    */

    public function totalTomes(): int
    {
        return $this->repository->countAllTomes();
    }

    public function totalSeries(): int
    {
        return $this->repository->countSeries();
    }

    public function averageNote(): ?float
    {
        return $this->repository->averageNote();
    }

    public function lastTome(): object|false
    {
        return $this->repository->findLastAdded();
    }

    /*
    |--------------------------------------------------------------------------
    | SERIES STATS
    |--------------------------------------------------------------------------
    */

    public function longestSeries(): object|false
    {
        return $this->repository->findLongestSeries();
    }

    public function topLongestSeries(
        int $limit = 5
    ): array {

        return $this->repository
            ->topLongestSeries($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | LOW STATS
    |--------------------------------------------------------------------------
    */

    public function lowRated(
        int $limit = 5
    ): array {

        return $this->repository
            ->findLowRatedMangas($limit);
    }

    public function lowJacquette(
        int $limit = 5
    ): array {

        return $this->repository
            ->findLowJacquetteMangas($limit);
    }

    public function lowLivreState(
        int $limit = 5
    ): array {

        return $this->repository
            ->findLowLivreStateMangas($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD DATA
    |--------------------------------------------------------------------------
    */

    public function dashboard(): array
    {
        return [

            'totalTomes' =>
                $this->totalTomes(),

            'totalSeries' =>
                $this->totalSeries(),

            'averageNote' =>
                $this->averageNote(),

            'lastTome' =>
                $this->lastTome(),

            'longestSeries' =>
                $this->longestSeries(),

            'topLongestSeries' =>
                $this->topLongestSeries(),

            'lowRatedMangas' =>
                $this->lowRated(),

            'lowJacquetteMangas' =>
                $this->lowJacquette(),

            'lowLivreStateMangas' =>
                $this->lowLivreState(),
        ];
    }
}