<?php

namespace App\Controllers;

use App\Models\MangaModel;

class MainController extends Controller
{
    /**
     * Retourne le modèle manga.
     */
    private function mangaModel(): MangaModel
    {
        return new MangaModel();
    }

    /**
     * Page d'accueil.
     * Route : /
     */
    public function index(): void
    {
        $this->title = 'Accueil';

        $mangaModel = $this->mangaModel();

        $totalTomes = $mangaModel->countAllTomes();
        $totalSeries = $mangaModel->countSeries();
        $averageNote = $mangaModel->averageNote();
        $lastTome = $mangaModel->findLastAdded();
        $longestSeries = $mangaModel->findLongestSeries();
        $topLongestSeries = $mangaModel->topLongestSeries();

        $lowRatedMangas = $mangaModel->findLowRatedMangas();
        $lowJacquetteMangas = $mangaModel->findLowJacquetteMangas();
        $lowLivreStateMangas = $mangaModel->findLowLivreStateMangas();

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