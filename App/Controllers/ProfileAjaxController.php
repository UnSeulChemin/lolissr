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
}