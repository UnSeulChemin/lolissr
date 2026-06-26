<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\UserTitle;
use App\DTO\Common\ServiceResult;
use App\Repositories\Auth\UserRepository;

use Framework\Http\Request;

final class ProfileAjaxController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        Request $request
    ) {
        parent::__construct($request);
    }

    public function titles(): never
    {
        $user = user();

        assert($user !== null);

        $titles =
            UserTitle::unlockedTitles(
                $user->level,
            );

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'titles' => $titles,
                ],
            ),
        );
    }

    public function updateTitle(): never
    {
        $user = user();

        assert($user !== null);

        $title =
            (string) $this->request->input(
                'title',
            );

        $availableTitles =
            UserTitle::unlockedTitles(
                $user->level,
            );

        if (
            ! in_array(
                $title,
                $availableTitles,
                true,
            )
        )
        {
            $this->jsonResult(
                ServiceResult::error(
                    message: 'Titre invalide',
                    status: 422,
                ),
            );
        }

        $this->userRepository->updateTitle(
            $user->id,
            $title,
        );

        $this->jsonResult(
            ServiceResult::success(
                message: 'Titre mis à jour',
                data: [
                    'title' => $title,
                ],
            ),
        );
    }

    public function avatars(): never
    {
        $avatars =
            $this->userRepository->avatars();

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'avatars' => $avatars,
                ],
            ),
        );
    }

    public function banners(): never
    {
        $banners =
            $this->userRepository->banners();

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'banners' => $banners,
                ],
            ),
        );
    }

    public function frames(): never
    {
        $frames =
            $this->userRepository->frames();

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'frames' => $frames,
                ],
            ),
        );
    }

    public function updateFrame(): never
    {
        $user = user();

        assert($user !== null);

        $frameName =
            (string) $this->request->input(
                'frame',
            );

        $frame =
            null;

        foreach (
            $this->userRepository->frames() as $item
        )
        {
            if (
                $item['frame']
                === $frameName
            )
            {
                $frame =
                    $item;

                break;
            }
        }

        if ($frame === null)
        {
            $this->jsonResult(
                ServiceResult::error(
                    message: 'Cadre invalide',
                    status: 422,
                ),
            );
        }

        $this->userRepository->updateFrame(
            $user->id,
            $frame['frame'],
            $frame['frame_extension'],
        );

        $this->jsonResult(
            ServiceResult::success(
                message: 'Cadre mis à jour',
                data: [
                    'frame' => $frame['frame'],
                    'frame_extension' => $frame['frame_extension'],
                ],
            ),
        );
    }

    public function updateAvatar(): never
    {
        $user = user();

        assert($user !== null);

        $avatarName =
            (string) $this->request->input(
                'avatar',
            );

        $avatar =
            null;

        foreach (
            $this->userRepository->avatars() as $item
        )
        {
            if (
                $item['avatar']
                === $avatarName
            )
            {
                $avatar =
                    $item;

                break;
            }
        }

        if ($avatar === null)
        {
            $this->jsonResult(
                ServiceResult::error(
                    message: 'Avatar invalide',
                    status: 422,
                ),
            );
        }

        $this->userRepository->updateAvatar(
            $user->id,
            $avatar['avatar'],
            $avatar['avatar_extension'],
        );

        $this->jsonResult(
            ServiceResult::success(
                message: 'Avatar mis à jour',
                data: [
                    'avatar' => $avatar['avatar'],
                    'avatar_extension' => $avatar['avatar_extension'],
                ],
            ),
        );
    }

    public function updateBanner(): never
    {
        $user = user();

        assert($user !== null);

        $bannerName =
            (string) $this->request->input(
                'banner',
            );

        $banner =
            null;

        foreach (
            $this->userRepository->banners() as $item
        )
        {
            if (
                $item['banner']
                === $bannerName
            )
            {
                $banner =
                    $item;

                break;
            }
        }

        if ($banner === null)
        {
            $this->jsonResult(
                ServiceResult::error(
                    message: 'Bannière invalide',
                    status: 422,
                ),
            );
        }

        $this->userRepository->updateBanner(
            $user->id,
            $banner['banner'],
            $banner['banner_extension'],
        );

        $this->jsonResult(
            ServiceResult::success(
                message: 'Bannière mise à jour',
                data: [
                    'banner' => $banner['banner'],
                    'banner_extension' => $banner['banner_extension'],
                ],
            ),
        );
    }
}
