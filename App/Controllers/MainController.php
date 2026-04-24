<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StatsService;

class MainController extends Controller
{
    private StatsService $statsService;

    public function __construct()
    {
        parent::__construct();

        $this->statsService =
            new StatsService();
    }

    /**
     * Page d'accueil.
     * Route : /
     */
    public function index(): void
    {
        $this->title = 'Accueil';

        $stats =
            $this->statsService
                ->dashboard();

        $this->render(
            'main/index',
            $stats
        );
    }
}