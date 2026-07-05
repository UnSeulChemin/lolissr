<?php

declare(strict_types=1);

namespace App\Controllers\Nendoroid;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Nendoroid\Responses\NendoroidData;
use App\Services\Nendoroid\NendoroidReadService;
use App\Services\Nendoroid\NendoroidWriteService;

use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class NendoroidAjaxController extends Controller
{
    private const WAIFUS_PATH = 'nendoroid/waifus';

    public function __construct(
        private readonly NendoroidReadService $nendoroidReadService,
        private readonly NendoroidWriteService $nendoroidWriteService,
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

        $data = $this->nendoroidReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment(
            'pages/nendoroid/waifus/ajax',
            [
                'nendoroids' => $data->nendoroids,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->totalPages,
            ]
        );
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
        $nendoroid = $this->resolveNendoroidOrFail(
            $slug,
            $numero
        );

        $result = $this->nendoroidWriteService->delete(
            $nendoroid->slug,
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

    private function resolveNendoroidOrFail(
        string $slug,
        int $numero
    ): NendoroidData
    {
        $nendoroid = $this->nendoroidReadService->one(
            $slug,
            $numero
        );

        if ($nendoroid === null)
        {
            throw new NotFoundException(
                'Nendoroid introuvable'
            );
        }

        return $nendoroid;
    }
}