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
        $page = $this->chinoisReadService->flashcardPage(false);

        $this->render('pages/chinois/flashcards/vocabulaire', [
            'vocabulaires' => $page['cards'],
            'flashcardTotal' => $page['total'],
        ]);
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Flashcards Grammaire';
        $page = $this->chinoisReadService->flashcardPage(true);

        $this->render('pages/chinois/flashcards/grammaire', [
            'grammaires' => $page['cards'],
            'flashcardTotal' => $page['total'],
        ]);
    }

    public function vocabulairePage(int $offset): never
    {
        $this->jsonResult(ServiceResult::success(data: $this->chinoisReadService->flashcardPage(false, $offset)));
    }

    public function grammairePage(int $offset): never
    {
        $this->jsonResult(ServiceResult::success(data: $this->chinoisReadService->flashcardPage(true, $offset)));
    }

    // Retained for open tabs running a previous JavaScript bundle.
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
