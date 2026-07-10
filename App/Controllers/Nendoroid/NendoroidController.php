<?php

declare(strict_types=1);

namespace App\Controllers\Nendoroid;

use App\Controllers\Controller;
use App\DTO\Nendoroid\Responses\NendoroidData;
use App\Http\Requests\Nendoroid\NendoroidCreateRequest;
use App\Http\Requests\Nendoroid\NendoroidUpdateRequest;
use App\Services\Nendoroid\NendoroidReadService;
use App\Services\Nendoroid\NendoroidWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\FormRequest;
use Framework\Http\Request;

final class NendoroidController extends Controller
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
    | PAGES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Nendoroids';

        $this->render('pages/nendoroid/index');
    }

    public function waifus(int $page = 1): never
    {
        $data = $this->nendoroidReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Nendoroids | Waifus'
            . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render('pages/nendoroid/waifus/index', [
            'nendoroids' => $data->nendoroids,
            'currentPage' => $data->currentPage,
            'totalWaifus' => $data->totalWaifus,
            'perPage' => $data->perPage,
            'totalPages' => $data->totalPages,
        ]);
    }

    public function showWaifu(
        string $slug,
        int $numero
    ): never
    {
        $nendoroid = $this->resolveNendoroidOrFail(
            $slug,
            $numero
        );

        $this->title = 'Nendoroids | ' . $nendoroid->waifu;

        $this->render('pages/nendoroid/waifus/waifu', [
            'nendoroid' => $nendoroid,
        ]);
    }

    public function create(): never
    {
        $this->title = 'Nendoroids | Ajouter';

        $this->render('pages/nendoroid/ajouter', [
            'form' => $this->formViewData(
                'nendoroid/ajouter',
                'nendoroid',
            ),
        ]);
    }

    public function store(NendoroidCreateRequest $request): never
    {
        $this->validateRequest($request);

        $result = $this->nendoroidWriteService->create(
            $request->dto(),
            $request->files()
        );

        $this->jsonResult($result);
    }

    public function edit(
        string $slug,
        int $numero
    ): never
    {
        $nendoroid = $this->resolveNendoroidOrFail(
            $slug,
            $numero
        );

        $this->title = 'Nendoroids | Modifier';

        $this->render(
            'pages/nendoroid/waifus/modifier',
            [
                'nendoroid' => $nendoroid,
                'form' => $this->formViewData(
                    sprintf(
                        '%s/%s/modifier/%d',
                        self::WAIFUS_PATH,
                        rawurlencode($nendoroid->slug),
                        $numero,
                    ),
                    $this->waifuUrl(
                        $nendoroid->slug,
                        $numero,
                    ),
                ),
            ]
        );
    }

    public function update(
        NendoroidUpdateRequest $request,
        string $slug,
        int $numero
    ): never
    {
        $nendoroid = $this->resolveNendoroidOrFail(
            $slug,
            $numero
        );

        $this->validateRequest($request);

        $result = $this->nendoroidWriteService->update(
            $nendoroid->slug,
            $numero,
            $request->dto()
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data
            );
        }

        $this->redirectWithSuccess(
            $this->waifuUrl(
                $nendoroid->slug,
                $numero
            ),
            $result->message
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function waifuUrl(string $slug, int $numero): string
    {
        return sprintf(
            '%s/%s/%d',
            self::WAIFUS_PATH,
            rawurlencode($slug),
            $numero
        );
    }

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
            throw new NotFoundException('Nendoroid introuvable');
        }

        return $nendoroid;
    }

    private function validateRequest(FormRequest $request): void
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }
    }
}