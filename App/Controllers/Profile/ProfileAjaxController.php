<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use App\Constants\UserTitle;
use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Services\Profile\ProfileImageCatalog;

use Framework\Http\Request;

final class ProfileAjaxController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProfileImageCatalog $imageCatalog,
        Request $request
    )
    {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | TITRES
    |--------------------------------------------------------------------------
    */

    public function titles(): never
    {
        $user = $this->user();

        $titles = UserTitle::unlockedTitles($user->level);

        $this->jsonResult(ServiceResult::success(data: ['titles' => $titles]));
    }

    public function updateTitle(): never
    {
        $user = $this->user();

        $title = $this->stringInput('title');

        $availableTitles = UserTitle::unlockedTitles($user->level);

        if (! in_array($title, $availableTitles, true))
        {
            $this->jsonResult(ServiceResult::error(message: 'Titre invalide', status: 422));
        }

        if (! $this->userRepository->updateTitle($user->id, $title))
        {
            $this->jsonResult(ServiceResult::error(message: 'Titre non enregistré', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Titre mis à jour',
            data: ['title' => $title]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | AVATARS
    |--------------------------------------------------------------------------
    */

    public function avatars(): never
    {
        $avatars = $this->imageCatalog->items('avatar');

        $this->jsonResult(ServiceResult::success(data: ['avatars' => $avatars]));
    }

    public function updateAvatar(): never
    {
        $user = $this->user();

        $avatar = $this->findItem(
            $this->imageCatalog->items('avatar'),
            'avatar',
            $this->stringInput('avatar')
        );

        if ($avatar === null)
        {
            $this->jsonResult(ServiceResult::error(message: 'Avatar invalide', status: 422));
        }

        if (! $this->userRepository->updateAvatar(
            $user->id,
            $avatar['avatar'],
            $avatar['avatar_extension']
        ))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Avatar mis à jour',
            data: [
                'avatar' => $avatar['avatar'],
                'avatar_extension' => $avatar['avatar_extension']
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | BANNIÈRES
    |--------------------------------------------------------------------------
    */

    public function banners(): never
    {
        $banners = $this->imageCatalog->items('banner');

        $this->jsonResult(ServiceResult::success(data: ['banners' => $banners]));
    }

    public function updateBanner(): never
    {
        $user = $this->user();

        $banner = $this->findItem(
            $this->imageCatalog->items('banner'),
            'banner',
            $this->stringInput('banner')
        );

        if ($banner === null)
        {
            $this->jsonResult(ServiceResult::error(message: 'Bannière invalide', status: 422));
        }

        if (! $this->userRepository->updateBanner(
            $user->id,
            $banner['banner'],
            $banner['banner_extension']
        ))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Bannière mise à jour',
            data: [
                'banner' => $banner['banner'],
                'banner_extension' => $banner['banner_extension']
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CADRES
    |--------------------------------------------------------------------------
    */

    public function frames(): never
    {
        $frames = $this->imageCatalog->framesForLevel($this->user()->level);

        $this->jsonResult(ServiceResult::success(data: ['frames' => $frames]));
    }

    public function updateFrame(): never
    {
        $user = $this->user();

        $frame = $this->findItem(
            $this->imageCatalog->framesForLevel($user->level),
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
                message: 'Ce cadre se débloque au niveau ' . $frame['required_level'],
                status: 422
            ));
        }

        if (! $this->userRepository->updateFrame(
            $user->id,
            $frame['frame'],
            $frame['frame_extension']
        ))
        {
            $this->jsonResult(ServiceResult::error(message: 'Personnalisation non enregistrée', status: 500));
        }

        $this->jsonResult(ServiceResult::success(
            message: 'Cadre mis à jour',
            data: [
                'frame' => $frame['frame'],
                'frame_extension' => $frame['frame_extension']
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

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
