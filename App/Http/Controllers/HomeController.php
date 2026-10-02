<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Cache\DashboardCache;

use Framework\Http\Request;

final class HomeController extends Controller
{
    public function __construct(
        private readonly DashboardCache $dashboardCache,
        Request $request
    ) {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // ACCUEIL
    // --------------------------------------------------------------------------

    public function index(): never
    {
        $this->title = 'Accueil';

        $this->render(
            'pages/home/index',
            [
                'stats' => $this->dashboardCache->get(),
            ]
        );
    }
}