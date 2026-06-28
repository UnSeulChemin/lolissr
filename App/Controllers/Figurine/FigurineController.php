<?php

declare(strict_types=1);

namespace App\Controllers\Figurine;

use App\Controllers\Controller;
use App\DTO\Figurine\Responses\FigurineData;
use App\Services\Figurine\FigurineReadService;

use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class FigurineController extends Controller
{
    public function __construct(
        private readonly FigurineReadService $figurineReadService,
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
            'figurines' => $this->figurineReadService->waifus(),
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

    public function edit(string $slug): never
    {
        $figurine = $this->resolveFigurineOrFail($slug);

        $this->title = 'Figurines | Modifier';

        $this->render('pages/figurine/waifus/modifier', [
            'figurine' => $figurine,
        ]);
    }

    public function store(): never
    {
        //
    }

    public function update(string $slug): never
    {
        //
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