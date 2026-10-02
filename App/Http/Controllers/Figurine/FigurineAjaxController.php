<?php

declare(strict_types=1);

namespace App\Http\Controllers\Figurine;

use App\Http\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Figurine\FigurineReadService;
use App\Services\Figurine\FigurineWriteService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Request;

final class FigurineAjaxController extends Controller
{
    private const WAIFUS_PATH = 'figurine/waifus';

    public function __construct(
        private readonly FigurineReadService $figurineReadService,
        private readonly FigurineWriteService $figurineWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): never
    {
        $searchData = $this->figurineReadService->search((string) $query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX WAIFUS PAGE
    |--------------------------------------------------------------------------
    */

    public function waifusPage(int $page = 1): never
    {
        $page = max(1, $page);

        $data = $this->figurineReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment(
            'pages/figurine/collection/partials/items',
            [
                'figurines' => $data->figurines,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->totalPages,
            ]
        );
    }

    public function updateCollectStatus(
        string $slug,
        int $numero
    ): never
    {
        $collectStatus = $this->binaryStatusInput('collectStatus');

        $result = $this->figurineWriteService->updateCollectStatus(
            $slug,
            $numero,
            $collectStatus
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(
        string $slug,
        int $numero
    ): never
    {
        $result = $this->figurineWriteService->delete(
            $slug,
            $numero
        );

        if (! $result->success)
        {
            $this->jsonResult($result);
        }

        $this->jsonResult(
            ServiceResult::success(
                message: $result->message,
                data: [
                    ...$result->data,
                    'redirect' => sprintf(
                        '%s/%s',
                        $this->baseUri,
                        self::WAIFUS_PATH
                    ),
                ],
                status: $result->status,
            )
        );
    }

}
