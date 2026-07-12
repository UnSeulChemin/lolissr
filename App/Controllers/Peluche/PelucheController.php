<?php

declare(strict_types=1);

namespace App\Controllers\Peluche;

use App\Controllers\Controller;
use App\DTO\Peluche\Responses\PelucheData;
use App\Http\Requests\Peluche\PelucheCreateRequest;
use App\Http\Requests\Peluche\PelucheUpdateRequest;
use App\Services\Peluche\PelucheReadService;
use App\Services\Peluche\PelucheWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\FormRequest;
use Framework\Http\Request;

final class PelucheController extends Controller
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
    | PAGES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Peluches';

        $this->render('pages/peluche/index');
    }

    public function waifus(int $page = 1): never
    {
        $data = $this->pelucheReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Peluches | Waifus'
            . ($data->currentPage > 1
                ? ' - Page ' . $data->currentPage
                : '');

        $this->render(
            'pages/peluche/waifus/index',
            [
                'peluches' => $data->peluches,
                'currentPage' => $data->currentPage,
                'totalWaifus' => $data->totalWaifus,
                'perPage' => $data->perPage,
                'totalPages' => $data->totalPages,
            ],
        );
    }

    public function showWaifu(
        string $slug,
        int $numero
    ): never
    {
        $peluche = $this->resolvePelucheOrFail(
            $slug,
            $numero,
        );

        $this->title = 'Peluches | ' . $peluche->waifu;

        $this->render(
            'pages/peluche/waifus/waifu',
            [
                'peluche' => $peluche,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): never
    {
        $this->title = 'Peluches | Ajouter';

        $this->render(
            'pages/peluche/ajouter',
            [
                'form' => $this->formViewData(
                    'peluche/ajouter',
                    'peluche',
                ),
            ],
        );
    }

    public function store(PelucheCreateRequest $request): never
    {
        $this->validateRequest($request);

        $result = $this->pelucheWriteService->create(
            $request->dto(),
            $request->files(),
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function edit(
        string $slug,
        int $numero
    ): never
    {
        $peluche = $this->resolvePelucheOrFail(
            $slug,
            $numero,
        );

        $this->title = 'Peluches | Modifier';

        $this->render(
            'pages/peluche/waifus/modifier',
            [
                'peluche' => $peluche,

                'form' => $this->formViewData(
                    sprintf(
                        '%s/%s/modifier/%d',
                        self::WAIFUS_PATH,
                        rawurlencode($peluche->slug),
                        $numero,
                    ),
                    $this->waifuUrl(
                        $peluche->slug,
                        $numero,
                    ),
                ),
            ],
        );
    }

    public function update(
        PelucheUpdateRequest $request,
        string $slug,
        int $numero
    ): never
    {
        $peluche = $this->resolvePelucheOrFail(
            $slug,
            $numero,
        );

        $this->validateRequest($request);

        $result = $this->pelucheWriteService->update(
            $peluche->slug,
            $numero,
            $request->dto(),
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data,
            );
        }

        $this->redirectWithSuccess(
            $this->waifuUrl(
                $peluche->slug,
                $numero,
            ),
            $result->message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function waifuUrl(
        string $slug,
        int $numero
    ): string
    {
        return sprintf(
            '%s/%s/%d',
            self::WAIFUS_PATH,
            rawurlencode($slug),
            $numero,
        );
    }

    private function resolvePelucheOrFail(
        string $slug,
        int $numero
    ): PelucheData
    {
        $peluche = $this->pelucheReadService->one(
            $slug,
            $numero,
        );

        if ($peluche === null)
        {
            throw new NotFoundException(
                'Peluche introuvable',
            );
        }

        return $peluche;
    }

    private function validateRequest(FormRequest $request): void
    {
        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }
    }
}