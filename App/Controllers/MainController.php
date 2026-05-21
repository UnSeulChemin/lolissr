<?php

declare(strict_types=1);

namespace App\Controllers;

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
