<?php

namespace App\Controllers;

use App\Models\MangaModel;

class MainController extends Controller
{
    /**
     * page d'accueil
     * route : /
     */
    public function index(): void
    {
        $this->title = 'Accueil';

        $mangaModel = new MangaModel();

        $totalTomes = $mangaModel->countAllTomes();
        $totalSeries = $mangaModel->countSeries();
        $lastTome = $mangaModel->findLastAdded();
        $longestSeries = $mangaModel->findLongestSeries();
        $bestRatedMangas = $mangaModel->findBestRatedMangas();
        $averageNote = $mangaModel->averageNote();
        $topLongestSeries = $mangaModel->topLongestSeries(5);

        $this->render('layout/index', [
            'totalTomes' => $totalTomes,
            'totalSeries' => $totalSeries,
            'lastTome' => $lastTome,
            'longestSeries' => $longestSeries,
            'bestRatedMangas' => $bestRatedMangas,
            'averageNote' => $averageNote,
            'topLongestSeries' => $topLongestSeries
        ]);
    }
}