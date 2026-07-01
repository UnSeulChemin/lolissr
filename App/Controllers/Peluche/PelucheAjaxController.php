<?php

declare(strict_types=1);

namespace App\Controllers\Peluche;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Peluche\Responses\PelucheData;
use App\Services\Peluche\PelucheReadService;
use App\Services\Peluche\PelucheWriteService;

use Framework\Exceptions\NotFoundException;
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
            'pages/peluche/waifus/ajax',
            [
                'peluches' => $data->peluches,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->compteur,
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
        $peluche = $this->resolvePelucheOrFail(
            $slug,
            $numero
        );

        $result = $this->pelucheWriteService->delete(
            $peluche->slug,
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

    private function resolvePelucheOrFail(
        string $slug,
        int $numero
    ): PelucheData
    {
        $peluche = $this->pelucheReadService->one(
            $slug,
            $numero
        );

        if ($peluche === null)
        {
            throw new NotFoundException(
                'Peluche introuvable'
            );
        }

        return $peluche;
    }
}