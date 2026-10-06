<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Profile\ProfileAchievements;
use App\Services\Profile\ProfileStatsService;
use App\Services\User\UserLevelService;

use Framework\Http\Request;
use Framework\Support\Str;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserLevelService $userLevelService,
        private readonly ProfileStatsService $profileStatsService,
        private readonly \App\Repositories\Profile\ProfileUnlockStatsRepository $unlockStats,
        Request $request
    )
    {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // PROFIL
    // --------------------------------------------------------------------------

    public function index(): never
    {
        $this->title = 'Profil';
        $user = user();
        assert($user instanceof User);
        $this->render('pages/profile/index', [
            'user' => $user,
            'achievements' => ProfileAchievements::forStats($this->unlockStats->forAchievements(), $user->level),
            'level' => $user->level,
            'currentXp' => $user->xp,
            'xpRequired' => $this->userLevelService->xpRequiredForLevel($user->level),
            'progress' => $this->userLevelService->progress($user)
        ]);
    }

    public function xp(?string $section = null): never
    {
        $this->title = 'Résumé de l’XP';

        $user = user();

        assert($user instanceof User);

        $stats = $this->profileStatsService->getStats($user);
        $achievements = ProfileAchievements::forStats($stats, $user->level);
        $section ??= $this->stringInput('section');
        $section = $this->resolveSection($section, ['succes', ...array_column($achievements, 'category')]);

        $this->render('pages/profile/xp', [
            'section' => $section,
            'achievements' => $achievements,
            'level' => $user->level,
            'currentXp' => $user->xp,
            'xpRequired' => $this->userLevelService->xpRequiredForLevel($user->level),

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

            'totalProfileXp' => $stats->totalXp,
            'achievementXp' => $stats->achievementXp
        ]);
    }

    // --------------------------------------------------------------------------
    // PERSONNALISATION
    // --------------------------------------------------------------------------

    public function achievements(?string $section = null): never
    {
        $this->title = 'Succès';
        $user = user();
        assert($user instanceof User);

        $achievements = ProfileAchievements::forStats($this->unlockStats->forAchievements(), $user->level);
        $section ??= $this->stringInput('section');
        $section = $this->resolveSection($section, array_column($achievements, 'category'));

        $this->render('pages/profile/achievements', ['achievements' => $achievements, 'section' => $section]);
    }

    public function customization(): never
    {
        $this->title = 'Personnalisation';

        $user = user();

        assert($user instanceof User);

        $this->render('pages/profile/customization', ['user' => $user]);
    }

    /** @param list<string> $categories */
    private function resolveSection(string $section, array $categories): string
    {
        $slug = Str::asciiSlug($section);
        foreach (array_unique($categories) as $category)
        {
            if (Str::asciiSlug($category) === $slug) return $category;
        }

        return 'tout';
    }
}
