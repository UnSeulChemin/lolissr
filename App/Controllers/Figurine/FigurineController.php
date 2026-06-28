<?php

declare(strict_types=1);

namespace App\Controllers\Figurine;

use App\Controllers\Controller;
use App\DTO\Figurine\Responses\FigurineData;
use App\Http\Requests\Figurine\FigurineCreateRequest;
use App\Services\Figurine\FigurineReadService;
use App\Services\Figurine\FigurineWriteService;

use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class FigurineController extends Controller
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
    | PAGES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Figurines';

        $this->render('pages/figurine/index');
    }

    public function waifus(int $page = 1): never
    {
        $this->title = 'Figurines | Waifus';

        $this->render('pages/figurine/waifus/index', [
            'figurines' => $this->figurineReadService->waifus($page),
        ]);
    }

    public function showWaifu(string $slug): never
    {
        $figurine = $this->resolveFigurineOrFail($slug);

        $this->title = 'Figurines | ' . $figurine->waifu;

        $this->render('pages/figurine/waifus/waifu', [
            'figurine' => $figurine,
        ]);
    }

    public function create(): never
    {
        $this->title = 'Figurines | Ajouter';

        $this->render('pages/figurine/ajouter');
    }

    public function store(FigurineCreateRequest $request): never
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }

        $result = $this->figurineWriteService->create(
            $request->dto(),
            $request->files()
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function resolveFigurineOrFail(string $slug): FigurineData
    {
        $figurine = $this->figurineReadService->one($slug);

        if ($figurine === null)
        {
            throw new NotFoundException('Figurine introuvable');
        }

        return $figurine;
    }
}