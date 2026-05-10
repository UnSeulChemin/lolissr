<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Services\Chinois\ChinoisReadService;

final class ChinoisController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $this->title = 'Chinois';

        $this->render('chinois/index');
    }

    public function mandarin(): void
    {
        $this->title = 'Chinois | Mandarin';

        $this->render('chinois/mandarin', [
            'vocabulaires' => $this->chinoisReadService->mandarin(),
        ]);
    }

    public function jinyu(): void
    {
        $this->title = 'Chinois | 晋语';

        $this->render('chinois/jinyu', [
            'vocabulaires' => $this->chinoisReadService->jinyu(),
        ]);
    }

    public function grammaire(): void
    {
        $this->title = 'Chinois | Grammaire';

        $this->render('chinois/grammaire');
    }

    public function hsk1(): void
    {
        $this->title = 'Chinois | Grammaire HSK1';

        $this->render('chinois/grammaire/hsk1');
    }

    public function hsk2(): void
    {
        $this->title = 'Chinois | Grammaire HSK2';

        $this->render('chinois/grammaire/hsk2');
    }

    public function hsk3(): void
    {
        $this->title = 'Chinois | Grammaire HSK3';

        $this->render('chinois/grammaire/hsk3');
    }

    public function hsk4(): void
    {
        $this->title = 'Chinois | Grammaire HSK4';

        $this->render('chinois/grammaire/hsk4');
    }

    public function flashcards(): void
    {
        $this->title = 'Chinois | Flashcards';

        $this->render('chinois/flashcards');
    }

    public function ajouter(): void
    {
        $this->title = 'Chinois | Ajouter';

        $this->render('chinois/ajouter');
    }
}