<?php

declare(strict_types=1);

namespace App\Controllers\Figurine;

use App\Controllers\Controller;
use App\DTO\Figurine\Responses\FigurineData;
use App\Http\Requests\Figurine\FigurineCreateRequest;
use App\Services\Figurine\FigurineReadService;
use App\Services\Figurine\FigurineWriteService;
use App\Http\Requests\Figurine\FigurineUpdateRequest;

use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;
use Framework\Http\FormRequest;
use Framework\Exceptions\BaseHttpException;

final class FigurineController extends Controller
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
    | PAGES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Figurine';

        $this->render('pages/figurine/index');
    }

    public function links(): never
    {
        $this->title = 'Figurine | Liens utiles';

        $this->render('pages/figurine/lien');
    }

    public function waifus(int $page = 1): never
    {
        $data = $this->figurineReadService->waifus($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Figurine | Waifus'
            . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render('pages/figurine/waifus/index', [
            'figurines' => $data->figurines,
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
        $figurine = $this->resolveFigurineOrFail(
            $slug,
            $numero
        );

        $this->title = 'Figurine | ' . $figurine->waifu;

        $this->render('pages/figurine/waifus/waifu', [
            'figurine' => $figurine,
        ]);
    }

    public function create(): never
    {
        $this->title = 'Figurine | Ajouter';

        $this->render('pages/figurine/ajouter', [
            'form' => $this->formViewData(
                'figurine/ajouter',
                'figurine',
            ),
        ]);
    }

    public function store(FigurineCreateRequest $request): never
    {
        $this->validateRequest($request);

        $result = $this->figurineWriteService->create(
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
        $figurine = $this->resolveFigurineOrFail(
            $slug,
            $numero
        );

        $this->title = 'Figurine | Modifier';

        $this->render(
            'pages/figurine/waifus/modifier',
            [
                'figurine' => $figurine,
                'form' => $this->formViewData(
                    sprintf(
                        '%s/%s/modifier/%d',
                        self::WAIFUS_PATH,
                        rawurlencode($figurine->slug),
                        $numero,
                    ),
                    $this->waifuUrl(
                        $figurine->slug,
                        $numero,
                    ),
                ),
            ]
        );
    }

    public function update(
        FigurineUpdateRequest $request,
        string $slug,
        int $numero
    ): never
    {
        $figurine = $this->resolveFigurineOrFail($slug, $numero);

        $this->validateRequest($request);

        $result = $this->figurineWriteService->update(
            $figurine->slug,
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

        $this->redirectWithSuccess($this->waifuUrl($figurine->slug, $numero), $result->message);
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
            throw new NotFoundException('Figurine introuvable');
        }

        return $figurine;
    }

    private function validateRequest(FormRequest $request): void
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }
    }
}
