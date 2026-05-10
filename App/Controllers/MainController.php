<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache\Cache;
use App\Services\StatsService;

final class MainController extends Controller
{
    public function __construct(
        private readonly StatsService $statsService
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $this->title = 'Accueil';

        $stats = Cache::remember(
            'home.dashboard',
            300,
            fn () => $this->statsService->dashboard()
        );

        $this->render('main/index', $stats);
    }
}