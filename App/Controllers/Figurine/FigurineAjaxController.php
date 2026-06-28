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
    public function __construct(
        private readonly FigurineReadService $figurineReadService,
        private readonly FigurineWriteService $figurineWriteService,
        Request $request
    ) {
        parent::__construct($request);
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

    public function delete(string $slug): never
    {
        $figurine = $this->resolveFigurineOrFail($slug);

        $result = $this->figurineWriteService->delete(
            $figurine->slug
        );

        $this->jsonResult(
            ServiceResult::success(
                message: $result->message,
                data: [
                    ...$result->data,
                    'redirect' => sprintf(
                        '%s/figurines/waifus',
                        $this->baseUri
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
        string $slug
    ): FigurineData
    {
        $figurine = $this->figurineReadService->one($slug);

        if ($figurine === null)
        {
            throw new NotFoundException(
                'Figurine introuvable'
            );
        }

        return $figurine;
    }
}