<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\StatsService;
use Framework\Cache\Cache;

final class MainController extends Controller
{
    public function __construct(
        private readonly StatsService $statsService,
    ) {
        parent::__construct();
    }

    public function index(): never
    {
        $this->title = 'Accueil';

        $stats = Cache::remember(
            'home.dashboard',
            300,
            fn () => $this->statsService
                ->dashboard(),
        );

        $this->render(
            'main/index',
            [
                'stats' => $stats,
            ],
        );
    }
}