<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Home\DashboardStats;
use App\Services\StatsService;
use Framework\Cache\Cache;
use Framework\Http\Request;

final class MainController extends Controller
{
    public function __construct(
        private readonly StatsService $statsService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Accueil';

        /** @var DashboardStats $stats */
        $stats = Cache::remember(
            key: 'home.dashboard',
            ttl: 300,
            callback: fn (): DashboardStats =>
                $this->statsService->dashboard(),
        );

        $this->render(
            'main/index',
            [
                'stats' => $stats,
            ],
        );
    }
}