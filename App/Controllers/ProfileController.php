<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Manga\MangaStatsRepository;
use App\Services\User\UserLevelService;
use App\Constants\UserXp;
use Framework\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserLevelService $userLevelService,
        private readonly MangaStatsRepository $mangaStatsRepository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Profil';

        $user = user();

        if ($user === null)
        {
            redirect('connexion');
        }

        $xpRequired =
            $this->userLevelService
                ->xpRequiredForLevel(
                    $user->level,
                );

        $progress =
            $this->userLevelService
                ->progress(
                    $user,
                );

        $readTomes =
            $this->mangaStatsRepository
                ->countRead();

        $completedSeries =
            $this->mangaStatsRepository
                ->countCompletedSeries();

        $this->render(
            'pages/profile/index',
            [
                'user' => $user,

                'level' =>
                    $user->level,

                'currentXp' =>
                    $user->xp,

                'xpRequired' =>
                    $xpRequired,

                'progress' =>
                    $progress,

                'readTomes' =>
                    $readTomes,

                'totalXp' =>
                    $readTomes
                    * UserXp::READ_TOME,

                'completedSeries' =>
                    $completedSeries,

                'seriesXp' =>
                    $completedSeries
                    * UserXp::COMPLETE_SERIES,
            ],
        );
    }
}