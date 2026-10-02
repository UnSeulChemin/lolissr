<?php

declare(strict_types=1);

namespace App\Http\Controllers\Artbook;

use App\Http\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Artbook\ArtbookReadService;
use App\Services\Artbook\ArtbookWriteService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Request;

final class ArtbookAjaxController extends Controller
{
    public function __construct(
        private readonly ArtbookReadService $artbookReadService,
        private readonly ArtbookWriteService $artbookWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }


    // =========================================
    // SEARCH
    // =========================================

    public function search(string|int $query = ''): never
    {
        $searchData = $this->artbookReadService->search(
            (string) $query
        );

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ],
            ),
        );
    }


    // =========================================
    // PAGINATION
    // =========================================

    public function page(int $page = 1): never
    {
        $page = max(1, $page);

        $data = $this->artbookReadService->artbooks(
            $page
        );

        if ($data === null)
        {
            throw new NotFoundException(
                'Page introuvable'
            );
        }

        $this->renderFragment(
            'pages/artbook/partials/items',
            [
                'artbooks' => $data->artbooks,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->totalPages,
            ]
        );
    }


    // =========================================
    // UPDATE READ STATUS
    // =========================================

    public function updateReadStatus(
        string $slug,
        int $numero
    ): never {
        $readStatus = $this->binaryStatusInput('readStatus');

        $result = $this->artbookWriteService->updateReadStatus(
            $slug,
            $numero,
            $readStatus
        );

        $this->jsonResult($result);
    }


    // =========================================
    // DELETE
    // =========================================

    public function delete(
        string $slug,
        int $numero
    ): never {
        $result = $this->artbookWriteService->delete(
            $slug,
            $numero
        );

        $this->jsonResult($result);
    }
}
