<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MangaRepository;

class MainController extends Controller
{
    /**
     * Retourne le repository manga.
     */
    private function mangaRepository(): MangaRepository
    {
        return new MangaRepository();
    }

    /**
     * Page d'accueil.
     * Route : /
     */
    public function index(): void
    {
        $this->title = 'Accueil';

        $mangaRepository = $this->mangaRepository();

        $totalTomes = $mangaRepository->countAllTomes();
        $totalSeries = $mangaRepository->countSeries();
        $averageNote = $mangaRepository->averageNote();
        $lastTome = $mangaRepository->findLastAdded();
        $longestSeries = $mangaRepository->findLongestSeries();
        $topLongestSeries = $mangaRepository->topLongestSeries();

        $lowRatedMangas = $mangaRepository->findLowRatedMangas();
        $lowJacquetteMangas = $mangaRepository->findLowJacquetteMangas();
        $lowLivreStateMangas = $mangaRepository->findLowLivreStateMangas();

        $this->render('main/index', [
            'totalTomes' => $totalTomes,
            'totalSeries' => $totalSeries,
            'averageNote' => $averageNote,
            'lastTome' => $lastTome,
            'longestSeries' => $longestSeries,
            'topLongestSeries' => $topLongestSeries,
            'lowRatedMangas' => $lowRatedMangas,
            'lowJacquetteMangas' => $lowJacquetteMangas,
            'lowLivreStateMangas' => $lowLivreStateMangas
        ]);
    }
}