<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Profile\ProfileStatsService;
use App\Services\User\UserLevelService;
use Framework\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserLevelService $userLevelService,
        private readonly ProfileStatsService $profileStatsService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Profil';

        $user = user();

        $stats =
            $this->profileStatsService
                ->getStats();

        $this->render(
            'pages/profile/index',
            [
                'user' =>
                    $user,

                'level' =>
                    $user->level,

                'currentXp' =>
                    $user->xp,

                'xpRequired' =>
                    $this->userLevelService
                        ->xpRequiredForLevel(
                            $user->level,
                        ),

                'progress' =>
                    $this->userLevelService
                        ->progress(
                            $user,
                        ),

                'readTomes' =>
                    $stats->readTomes,

                'tomeXp' =>
                    $stats->tomeXp,

                'completedSeries' =>
                    $stats->completedSeries,

                'seriesXp' =>
                    $stats->seriesXp,

                'vocabularyLearned' =>
                    $stats->vocabularyLearned,

                'vocabularyXp' =>
                    $stats->vocabularyXp,

                'grammarLearned' =>
                    $stats->grammarLearned,

                'grammarXp' =>
                    $stats->grammarXp,

                'totalProfileXp' =>
                    $stats->totalXp,
            ],
        );
    }
}