<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\UserTitle;
use App\DTO\Common\ServiceResult;

use Framework\Http\Request;

final class ProfileAjaxController extends Controller
{
    public function __construct(
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
        $titleId = (int) $this->request->input('titleId');

        $this->jsonResult(
            ServiceResult::success(
                message: 'Titre mis à jour',
                data: [
                    'titleId' => $titleId,
                ],
            ),
        );
    }
}