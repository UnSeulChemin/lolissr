<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chinois;

use App\Http\Controllers\Controller;
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
        $page = $this->chinoisReadService->flashcardCursor(false);

        $this->render('pages/chinois/flashcards/vocabulaire', [
            'vocabulaires' => $page['cards'],
            'flashcardTotal' => $page['total'],
        ]);
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Flashcards Grammaire';
        $page = $this->chinoisReadService->flashcardCursor(true);

        $this->render('pages/chinois/flashcards/grammaire', [
            'grammaires' => $page['cards'],
            'flashcardTotal' => $page['total'],
        ]);
    }

    public function vocabulairePage(int $offset): never
    {
        $this->jsonResult(ServiceResult::success(data: $this->chinoisReadService->flashcardPage(false, $offset)));
    }

    public function vocabulaireCursor(string $direction, int $id): never
    {
        $this->cursor(false, $direction, $id);
    }

    public function grammaireCursor(string $direction, int $id): never
    {
        $this->cursor(true, $direction, $id);
    }

    private function cursor(bool $grammar, string $direction, int $id): never
    {
        if (! in_array($direction, ['next', 'previous'], true))
        {
            throw new \Framework\Http\Exceptions\NotFoundException('Direction introuvable');
        }
        $this->jsonResult(ServiceResult::success(data: $this->chinoisReadService->flashcardCursor($grammar, $id, $direction === 'previous')));
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
