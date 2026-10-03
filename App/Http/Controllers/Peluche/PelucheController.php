<?php

declare(strict_types=1);

namespace App\Http\Controllers\Peluche;

use App\Http\Controllers\Controller;
use App\DTO\Peluche\Responses\PelucheData;
use App\Http\Requests\Peluche\PelucheCreateRequest;
use App\Http\Requests\Peluche\PelucheUpdateRequest;
use App\Services\Peluche\PelucheReadService;
use App\Services\Peluche\PelucheWriteService;

use Framework\Http\Exceptions\BaseHttpException;
use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Request;

final class PelucheController extends Controller
{
    private const WAIFUS_PATH = 'peluche/peluches';

    public function __construct(
        private readonly PelucheReadService $pelucheReadService,
        private readonly PelucheWriteService $pelucheWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // --------------------------------------------------------------------------
    // PAGES PUBLIQUES
    // --------------------------------------------------------------------------

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

        $this->title = 'Peluches | Peluches'
            . ($data->currentPage > 1
                ? ' - Page ' . $data->currentPage
                : '');

        $this->render(
            'pages/peluche/collection/index',
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
            'pages/peluche/collection/show',
            [
                'peluche' => $peluche,
            ],
        );
    }

    // --------------------------------------------------------------------------
    // CRÉATION
    // --------------------------------------------------------------------------

    public function create(): never
    {
        $this->title = 'Peluches | Ajouter';

        $this->render(
            'pages/peluche/create',
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

    // --------------------------------------------------------------------------
    // MISE À JOUR
    // --------------------------------------------------------------------------

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
            'pages/peluche/collection/edit',
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
                statusCode: $result->status,
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

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

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
}