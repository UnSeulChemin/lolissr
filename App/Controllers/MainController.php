<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache\Cache;
use App\Services\StatsService;

final class MainController extends Controller
{
    private StatsService $statsService;

    public function __construct()
    {
        parent::__construct();

        $this->statsService = app(StatsService::class);
    }

    public function index(): void
    {
        $this->title = 'Accueil';

        $stats = Cache::remember(
            'home.dashboard',
            300,
            fn () => $this->statsService->dashboard()
        );

        $this->render(
            'main/index',
            $stats
        );
    }
}