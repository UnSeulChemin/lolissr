<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Chinois\ChinoisReadService;

use Framework\Http\Request;

final class FlashcardsController extends Controller
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

        $this->render('pages/chinois/flashcards/index');
    }

    public function vocabulaire(): never
    {
        $this->title = 'Chinois | Flashcards Vocabulaire';

        $this->render('pages/chinois/flashcards/vocabulaire', [
            'vocabulaires' => $this->chinoisReadService->vocabulaireFlashcards(),
            'flashcardIds' => $this->chinoisReadService->flashcardIds(false),
        ]);
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Flashcards Grammaire';

        $this->render('pages/chinois/flashcards/grammaire', [
            'grammaires' => $this->chinoisReadService->grammaireFlashcards(),
            'flashcardIds' => $this->chinoisReadService->flashcardIds(true),
        ]);
    }

    public function vocabulaireBatch(int $id): never
    {
        $this->jsonResult(ServiceResult::success(data: [
            'cards' => $this->chinoisReadService->vocabulaireFlashcards($id),
        ]));
    }

    public function grammaireBatch(int $id): never
    {
        $this->jsonResult(ServiceResult::success(data: [
            'cards' => $this->chinoisReadService->grammaireFlashcards($id),
        ]));
    }
}
