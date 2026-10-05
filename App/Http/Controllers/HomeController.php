<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Cache\DashboardCache;

use Framework\Http\Request;

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

        $releases = $this->releases->all();
        $future = array_values(array_filter($releases, static fn ($release): bool => $release->isUpcoming));
        $this->render('pages/home/index', ['stats' => $this->dashboardCache->get(), 'releaseUpcomingCount' => count($future), 'releaseMissingCount' => count($releases) - count($future), 'nextRelease' => $future[0] ?? null]);
    }
}
