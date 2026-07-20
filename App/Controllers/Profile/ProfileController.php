<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use App\Controllers\Controller;
use App\Models\User;
use App\Services\Profile\ProfileStatsService;
use App\Services\User\UserLevelService;

use Framework\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserLevelService $userLevelService,
        private readonly ProfileStatsService $profileStatsService,
        Request $request
    )
    {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | PROFIL
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Profil';

        $user = user();

        assert($user instanceof User);

        $stats = $this->profileStatsService->getStats();

        $this->render('pages/profile/index', [
            'user' => $user,
            'level' => $user->level,
            'currentXp' => $user->xp,
            'xpRequired' => $this->userLevelService->xpRequiredForLevel($user->level),
            'progress' => $this->userLevelService->progress($user),

            'readTomes' => $stats->readTomes,
            'tomeXp' => $stats->tomeXp,

            'completedSeries' => $stats->completedSeries,
            'seriesXp' => $stats->seriesXp,

            'readArtbooks' => $stats->readArtbooks,
            'artbookXp' => $stats->artbookXp,

            'figurinesCollected' => $stats->figurinesCollected,
            'figurinesXp' => $stats->figurinesXp,

            'nendoroidsCollected' => $stats->nendoroidsCollected,
            'nendoroidsXp' => $stats->nendoroidsXp,

            'peluchesCollected' => $stats->peluchesCollected,
            'peluchesXp' => $stats->peluchesXp,

            'vocabularyLearned' => $stats->vocabularyLearned,
            'vocabularyXp' => $stats->vocabularyXp,

            'grammarLearned' => $stats->grammarLearned,
            'grammarXp' => $stats->grammarXp,

            'totalProfileXp' => $stats->totalXp
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PERSONNALISATION
    |--------------------------------------------------------------------------
    */

    public function customization(): never
    {
        $this->title = 'Personnalisation';

        $user = user();

        assert($user instanceof User);

        $this->render('pages/profile/personnalisation', ['user' => $user]);
    }
}