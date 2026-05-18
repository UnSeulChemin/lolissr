<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Core\Http\Request;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;

final class ChinoisController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository,
    ) {
        parent::__construct();
    }

    public function index(
        Request $request
    ): void {
        $this->title = 'Chinois';

        $this->render('chinois/index');
    }

    public function mandarin(
        Request $request
    ): void {
        $this->title = 'Chinois | Mandarin';

        $this->render('chinois/mandarin', [
            'vocabulaires' => $this->chinoisReadService->mandarin(),
        ]);
    }

    public function jinyu(
        Request $request
    ): void {
        $this->title = 'Chinois | 晋语';

        $this->render('chinois/jinyu', [
            'vocabulaires' => $this->chinoisReadService->jinyu(),
        ]);
    }

    public function grammaire(
        Request $request
    ): void {
        $this->title = 'Chinois | Grammaire';

        $this->render('chinois/grammaire');
    }

    public function hsk1(
        Request $request
    ): void {
        $this->title = 'Chinois | Grammaire HSK1';

        $grammaires = $this->chinoisGrammaireRepository
            ->findByLevel('HSK1');

        $this->render('chinois/grammaire/hsk1', [
            'grammaires' => $grammaires,
        ]);
    }

    public function hsk2(
        Request $request
    ): void {
        $this->title = 'Chinois | Grammaire HSK2';

        $grammaires = $this->chinoisGrammaireRepository
            ->findByLevel('HSK2');

        $this->render('chinois/grammaire/hsk2', [
            'grammaires' => $grammaires,
        ]);
    }

    public function hsk3(
        Request $request
    ): void {
        $this->title = 'Chinois | Grammaire HSK3';

        $grammaires = $this->chinoisGrammaireRepository
            ->findByLevel('HSK3');

        $this->render('chinois/grammaire/hsk3', [
            'grammaires' => $grammaires,
        ]);
    }

    public function hsk4(
        Request $request
    ): void {
        $this->title = 'Chinois | Grammaire HSK4';

        $grammaires = $this->chinoisGrammaireRepository
            ->findByLevel('HSK4');

        $this->render('chinois/grammaire/hsk4', [
            'grammaires' => $grammaires,
        ]);
    }

    public function flashcards(
        Request $request
    ): void {
        $this->title = 'Chinois | Flashcards';

        $this->render('chinois/flashcards');
    }

    public function ajouter(
        Request $request
    ): void {
        $this->title = 'Chinois | Ajouter';

        $this->render('chinois/ajouter');
    }
}