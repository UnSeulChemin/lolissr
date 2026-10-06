<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use App\Cache\Home\DashboardCache;
use App\Http\Controllers\Controller;

use Framework\Http\Requests\Request;

final class HomeController extends Controller
{
    public function __construct(private readonly DashboardCache $dashboardCache, private readonly \App\Services\Manga\UpcomingMangaService $releases, Request $request)
    {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // ACCUEIL
    // --------------------------------------------------------------------------

    public function index(): never
    {
        $this->title = 'Accueil';

        $releases = $this->releases->summary();
        $this->render('pages/home/index', ['stats' => $this->dashboardCache->get(), 'releaseUpcomingCount' => $releases['upcomingCount'], 'releaseMissingCount' => $releases['missingCount'], 'nextRelease' => $releases['nextRelease']]);
    }
}
