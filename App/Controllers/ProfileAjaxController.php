<?php

declare(strict_types=1);

namespace App\Controllers;

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
        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'titles' => [
                        [
                            'id' => 1,
                            'name' => 'Explorateur',
                        ],
                    ],
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