<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Constants\Profile\ProfileTitles;
use App\DTO\Common\ServiceResult;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Profile\ProfileUnlockStatsRepository;
use App\Services\Profile\ProfileImageCatalog;

use Framework\Http\Requests\Request;

final class ProfileAjaxController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProfileImageCatalog $imageCatalog,
        private readonly ProfileUnlockStatsRepository $unlockStats,
        Request $request
    )
    {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // TITRES
    // --------------------------------------------------------------------------

    public function titles(): never
    {
        $stats = $this->unlockStats->forTitles();
        $user = $this->user();

        $titles = ProfileTitles::titlesForLevel($user->level, $stats->figurinesCollected, $stats->readArtbooks, $stats->readTomes, $stats->completedSeries, $stats->nendoroidsCollected, $stats->vocabularyLearned, $stats->grammarLearned);

        $this->jsonResult(ServiceResult::success(data: ['titles' => $titles]));
    }

    public function updateTitle(): never
    {
        $stats = $this->unlockStats->forTitles();
        $user = $this->user();

        $title = $this->stringInput('title');

        $selectedTitle = $this->findItem(
            ProfileTitles::titlesForLevel($user->level, $stats->figurinesCollected, $stats->readArtbooks, $stats->readTomes, $stats->completedSeries, $stats->nendoroidsCollected, $stats->vocabularyLearned, $stats->grammarLearned),
            'title',
            $title
        );

        if ($selectedTitle === null || ! $selectedTitle['unlocked'])
        {
            $this->jsonResult(ServiceResult::error(message: 'Titre invalide', status: 422));
        }

        if (! $this->userRepository->updateTitle($user->id, $title))
        {
            $this->jsonResult(ServiceResult::error(message: 'Titre non enregistré', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Titre mis à jour',
            data: ['title' => $title, 'style' => $selectedTitle['style']]
        ));
    }

    // --------------------------------------------------------------------------
    // AVATARS
    // --------------------------------------------------------------------------

    public function avatars(): never
    {
        $avatars = $this->availableAvatars();

        $this->jsonResult(ServiceResult::success(data: ['avatars' => $avatars]));
    }

    public function updateAvatar(): never
    {
        $user = $this->user();

        $avatar = $this->findItem($this->availableAvatars(), 'avatar', $this->stringInput('avatar'));

        if ($avatar === null)
        {
            $this->jsonResult(ServiceResult::error(message: 'Avatar invalide', status: 422));
        }

        if (! $avatar['unlocked'])
        {
            $this->jsonResult(ServiceResult::error(message: 'Condition de déblocage : ' . $avatar['requirement'], status: 422));
        }

        if (! $this->userRepository->updateAvatar($user->id, $avatar['avatar'], $avatar['avatar_extension']))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Avatar mis à jour',
            data: ['avatar' => $avatar['avatar'], 'avatar_extension' => $avatar['avatar_extension']]
        ));
    }

    // --------------------------------------------------------------------------
    // BANNIÈRES
    // --------------------------------------------------------------------------

    public function banners(): never
    {
        $stats = $this->unlockStats->forBanners();
        $banners = $this->imageCatalog->bannersForLevel($this->user()->level, $stats->readTomes, $stats->nendoroidsCollected, $stats->peluchesCollected, $stats->vocabularyLearned, $stats->grammarLearned);

        $this->jsonResult(ServiceResult::success(data: ['banners' => $banners]));
    }

    public function updateBanner(): never
    {
        $user = $this->user();

        $stats = $this->unlockStats->forBanners();

        $banner = $this->findItem(
            $this->imageCatalog->bannersForLevel($user->level, $stats->readTomes, $stats->nendoroidsCollected, $stats->peluchesCollected, $stats->vocabularyLearned, $stats->grammarLearned),
            'banner',
            $this->stringInput('banner')
        );

        if ($banner === null)
        {
            $this->jsonResult(ServiceResult::error(message: 'Bannière invalide', status: 422));
        }

        if (! $banner['unlocked'])
        {
            $this->jsonResult(ServiceResult::error(
                message: 'Condition de déblocage : ' . $banner['requirement'],
                status: 422
            ));
        }

        if (! $this->userRepository->updateBanner($user->id, $banner['banner'], $banner['banner_extension']))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Bannière mise à jour',
            data: ['banner' => $banner['banner'], 'banner_extension' => $banner['banner_extension']]
        ));
    }

    // --------------------------------------------------------------------------
    // CADRES
    // --------------------------------------------------------------------------

    public function frames(): never
    {
        $stats = $this->unlockStats->forFrames();
        $frames = $this->imageCatalog->framesForLevel($this->user()->level, $stats->figurinesCollected, $stats->readArtbooks, $stats->readTomes, $stats->nendoroidsCollected, $stats->peluchesCollected, $stats->vocabularyLearned, $stats->grammarLearned);

        $this->jsonResult(ServiceResult::success(data: ['frames' => $frames]));
    }

    public function updateFrame(): never
    {
        $stats = $this->unlockStats->forFrames();
        $user = $this->user();

        $frame = $this->findItem(
            $this->imageCatalog->framesForLevel($user->level, $stats->figurinesCollected, $stats->readArtbooks, $stats->readTomes, $stats->nendoroidsCollected, $stats->peluchesCollected, $stats->vocabularyLearned, $stats->grammarLearned),
            'frame',
            $this->stringInput('frame')
        );

        if ($frame === null)
        {
            $this->jsonResult(ServiceResult::error(message: 'Cadre invalide', status: 422));
        }

        if (! $frame['unlocked'])
        {
            $this->jsonResult(ServiceResult::error(
                message: 'Condition de déblocage : ' . $frame['requirement'],
                status: 422
            ));
        }

        if (! $this->userRepository->updateFrame($user->id, $frame['frame'], $frame['frame_extension']))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Cadre mis à jour',
            data: ['frame' => $frame['frame'], 'frame_extension' => $frame['frame_extension']]
        ));
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    /** @return list<array{avatar: string, avatar_extension: string, unlocked: bool, requirement: string}> */
    private function availableAvatars(): array
    {
        $achievements = \App\Services\Profile\ProfileAchievementCatalog::forStats($this->unlockStats->forAchievements(), $this->user()->level);
        $count = count(array_filter($achievements, static fn (array $item): bool => $item['category'] !== 'Succès' && $item['unlocked']));

        return $this->imageCatalog->avatarsForAchievements($count);
    }

    private function user(): User
    {
        $user = user();

        assert($user instanceof User);

        return $user;
    }

    /**
     * @template T of array<string, mixed>
     * @param array<int, T> $items
     *
     * @return T|null
     */
    private function findItem(array $items, string $key, string $value): ?array
    {
        foreach ($items as $item)
        {
            if ($item[$key] === $value)
            {
                return $item;
            }
        }

        return null;
    }
}
