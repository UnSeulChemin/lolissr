<?php

declare(strict_types=1);

namespace App\Controllers\Figurine;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Figurine\Responses\FigurineData;
use App\Services\Figurine\FigurineReadService;
use App\Services\Figurine\FigurineWriteService;

use Framework\Exceptions\NotFoundException;
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

    public function search(string $query = ''): never
    {
        $searchData = $this->figurineReadService->search($query);

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

        $this->renderFragment('pages/figurine/waifus/ajax', [
            'figurines' => $data->figurines,
            'currentPage' => $data->currentPage,
            'totalPages' => $data->compteur,
        ]);
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
        $figurine = $this->resolveFigurineOrFail(
            $slug,
            $numero
        );

        $result = $this->figurineWriteService->delete(
            $figurine->slug,
            $numero
        );

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

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function resolveFigurineOrFail(
        string $slug,
        int $numero
    ): FigurineData
    {
        $figurine = $this->figurineReadService->one(
            $slug,
            $numero
        );

        if ($figurine === null)
        {
            throw new NotFoundException(
                'Figurine introuvable'
            );
        }

        return $figurine;
    }
}