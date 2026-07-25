<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Stats\DashboardCache;

use Framework\Http\Request;

final class MainController extends Controller
{
    public function __construct(
        private readonly DashboardCache $dashboardCache,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCUEIL
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Accueil';

        $this->render(
            'pages/main/index',
            [
                'stats' => $this->dashboardCache->get(),
            ]
        );
    }
}