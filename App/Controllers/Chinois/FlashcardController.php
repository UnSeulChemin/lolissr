<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Services\Chinois\ChinoisReadService;

use Framework\Http\Request;

final class FlashcardController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // FLASHCARDS
    // =========================================

    public function index(): never
    {
        $this->title = 'Chinois | Flashcards';

        $this->render(
            'pages/chinois/flashcards/index'
        );
    }

    public function vocabulaire(): never
    {
        $this->title = 'Chinois | Flashcards Vocabulaire';

        $this->render(
            'pages/chinois/flashcards/vocabulaire',
            [
                'vocabulaires' => $this->chinoisReadService->flashcardsVocabulaire(),
            ]
        );
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Flashcards Grammaire';

        $this->render(
            'pages/chinois/flashcards/grammaire',
            [
                'grammaires' => $this->chinoisReadService->flashcardsGrammaire(),
            ]
        );
    }
}