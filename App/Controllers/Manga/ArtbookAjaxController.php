<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Responses\ArtbookData;
use App\Services\Manga\ArtbookReadService;
use App\Services\Manga\ArtbookWriteService;

use Framework\Exceptions\NotFoundException;
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
            'pages/manga/artbooks/ajax',
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
        $artbook = $this->resolveArtbookOrFail(
            $slug,
            $numero
        );

        $readStatus = (int) $this->request->input(
            'readStatus',
            0
        );

        $result = $this->artbookWriteService->updateReadStatus(
            $artbook->slug,
            $artbook->numero,
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
        $artbook = $this->resolveArtbookOrFail(
            $slug,
            $numero
        );

        $result = $this->artbookWriteService->delete(
            $artbook->slug,
            $artbook->numero
        );

        $this->jsonResult($result);
    }


    // =========================================
    // HELPERS
    // =========================================

    private function resolveArtbookOrFail(
        string $slug,
        int $numero
    ): ArtbookData {
        return $this->artbookReadService->one(
            $slug,
            $numero
        )
        ?? throw new NotFoundException(
            'Artbook introuvable'
        );
    }
}