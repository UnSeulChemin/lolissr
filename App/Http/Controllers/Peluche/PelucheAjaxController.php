<?php

declare(strict_types=1);

namespace App\Http\Controllers\Peluche;

use App\Http\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Peluche\PelucheReadService;
use App\Services\Peluche\PelucheWriteService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Request;

final class PelucheAjaxController extends Controller
{
    private const WAIFUS_PATH = 'peluche/waifus';

    public function __construct(
        private readonly PelucheReadService $pelucheReadService,
        private readonly PelucheWriteService $pelucheWriteService,
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
        $searchData = $this->pelucheReadService->search((string) $query);

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

        $data = $this->pelucheReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment(
            'pages/peluche/collection/partials/items',
            [
                'peluches' => $data->peluches,
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

        $result = $this->pelucheWriteService->updateCollectStatus(
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
        $result = $this->pelucheWriteService->delete(
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
