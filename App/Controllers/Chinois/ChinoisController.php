<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Http\Requests\Chinois\ChinoisVocabulaireCreateRequest;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class ChinoisController extends Controller
{
    private const HSK_LEVELS = [1, 2, 3, 4];

    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisWriteService $chinoisWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function vocabulaireOrFail(
        int $id,
    ): ChinoisVocabulaireData
    {
        return
            $this->chinoisReadService
                ->vocabulaire($id)
            ?? throw new NotFoundException(
                'Vocabulaire introuvable',
            );
    }

    private function grammaireOrFail(
        int $id,
    ): ChinoisGrammaireData
    {
        return
            $this->chinoisReadService
                ->grammaire($id)
            ?? throw new NotFoundException(
                'Grammaire introuvable',
            );
    }

    private function renderEditVocabulaire(
        int $id,
        string $returnTo,
    ): never {

        $this->title =
            'Chinois | Modifier du vocabulaire';

        $this->render(
            'pages/chinois/vocabulaire/modifier',
            [
                'vocabulaire' =>
                    $this->vocabulaireOrFail(
                        $id,
                    ),

                'returnTo' =>
                    $returnTo,
            ],
        );
    }

    private function renderEditGrammaire(
        int $id,
        string $returnTo,
    ): never {

        $this->title =
            'Chinois | Modifier une grammaire';

        $this->render(
            'pages/chinois/grammaire/modifier',
            [
                'grammaire' =>
                    $this->grammaireOrFail(
                        $id,
                    ),

                'returnTo' =>
                    $returnTo,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pages principales
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Chinois';
        $this->render('pages/chinois/index');
    }

    public function vocabulaire(): never
    {
        $this->title = 'Chinois | Vocabulaire';
        $this->render('pages/chinois/vocabulaire/index');
    }

    public function langue(string $langue): never
    {

        $langue = mb_strtolower($langue);

        $vocabulaires =
            match ($langue)
            {
                'mandarin' =>
                    $this->chinoisReadService
                        ->mandarin(),

                'jinyu' =>
                    $this->chinoisReadService
                        ->jinyu(),

                default =>
                    throw new NotFoundException(
                        'Langue introuvable',
                    ),
            };

        $this->title = 'Chinois | ' . ($langue === 'jinyu' ? '晋语' : 'Mandarin');

        $this->render('pages/chinois/vocabulaire/langue',
            [
                'langue' =>
                    $langue,

                'vocabulaires' =>
                    $vocabulaires,
            ],
        );
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Grammaire';
        $this->render('pages/chinois/grammaire/index');
    }

    public function hsk(int $level): never
    {
        if (! in_array($level, self::HSK_LEVELS, true))
        {
            throw new NotFoundException(
                'Niveau HSK introuvable',
            );
        }

        $hskLevel =
            "HSK{$level}";

        $this->title =
            'Chinois | Grammaire '
            . $hskLevel;

        $this->render(
            'pages/chinois/grammaire/hsk',
            [
                'grammaires' =>
                    $this->chinoisReadService
                        ->hsk($hskLevel),

                'level' =>
                    (string) $level,
            ],
        );
    }

    public function flashcards(): never
    {
        $this->title = 'Chinois | Flashcards';
        $this->render('pages/chinois/flashcards/index');
    }

    public function flashcardsVocabulaire(): never
    {
        $this->title =
            'Chinois | Flashcards Vocabulaire';

        $this->render(
            'pages/chinois/flashcards/vocabulaire',
            [
                'vocabulaires' =>
                    $this->chinoisReadService
                        ->flashcardsVocabulaire(),
            ],
        );
    }

    public function editFlashcardVocabulaire(
        int $id,
    ): never {

        $this->renderEditVocabulaire(
            $id,
            'chinois/flashcards/vocabulaire',
        );
    }

    public function updateFlashcardVocabulaire(
        ChinoisVocabulaireCreateRequest $request,
        int $id,
    ): never {

        $this->vocabulaireOrFail(
            $id,
        );

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $this->chinoisWriteService
            ->updateVocabulaire(
                $id,
                $request->dto(),
            );

        $this->redirectWithSuccess(
            'chinois/flashcards/vocabulaire',
            'Vocabulaire modifié.',
        );
    }

    public function flashcardsGrammaire(): never
    {
        $this->title =
            'Chinois | Flashcards Grammaire';

        $this->render(
            'pages/chinois/flashcards/grammaire',
            [
                'grammaires' =>
                    $this->chinoisReadService
                        ->flashcardsGrammaire(),
            ],
        );
    }

    public function editFlashcardGrammaire(
        int $id,
    ): never {

        $this->renderEditGrammaire(
            $id,
            'chinois/flashcards/grammaire',
        );
    }

    public function updateFlashcardGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $id,
    ): never {

        $this->grammaireOrFail(
            $id,
        );

        if ($request->fails()) {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $this->chinoisWriteService
            ->updateGrammaire(
                $id,
                $request->dto(),
            );

        $this->redirectWithSuccess(
            'chinois/flashcards/grammaire',
            'Grammaire modifiée.',
        );
    }

    public function ajouter(): never
    {
        $this->title = 'Chinois | Ajouter';
        $this->render('pages/chinois/ajouter/index');
    }

    public function createGrammaire(): never
    {
        $this->title = 'Chinois | Ajouter une grammaire';
        $this->render('pages/chinois/ajouter/grammaire');
    }

    public function createVocabulaire(): never
    {
        $this->title = 'Chinois | Ajouter du vocabulaire';
        $this->render('pages/chinois/ajouter/vocabulaire');
    }

    /*
    |--------------------------------------------------------------------------
    | Création / Mise à jour
    |--------------------------------------------------------------------------
    */

    public function storeGrammaire(ChinoisGrammaireCreateRequest $request): never
    {
        if ($request->fails()) {
            throw new ValidationException($request->errors());
        }

        $this->jsonResult($this->chinoisWriteService->createGrammaire($request->dto()));
    }

    public function storeVocabulaire(ChinoisVocabulaireCreateRequest $request): never
    {
        if ($request->fails()) {
            throw new ValidationException($request->errors());
        }

        $this->jsonResult($this->chinoisWriteService->createVocabulaire($request->dto()));
    }

    public function editGrammaire(
        int $_level,
        int $id,
    ): never {

        $this->renderEditGrammaire(
            $id,
            (string) $this->request
                ->input(
                    'return_to',
                    '',
                ),
        );
    }

    public function editVocabulaire(
        string $_langue,
        int $id,
    ): never {

        $this->renderEditVocabulaire(
            $id,
            (string) $this->request
                ->input(
                    'return_to',
                    '',
                ),
        );
    }

    public function updateGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $_level,
        int $id,
    ): never {

        $this->grammaireOrFail(
            $id,
        );

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $dto =
            $request->dto();

        $this->chinoisWriteService
            ->updateGrammaire(
                $id,
                $dto,
            );

        $level =
            substr(
                $dto->niveau,
                3,
            );

        $returnTo =
            (string) $this->request
                ->input(
                    'return_to',
                    '',
                );

        $this->redirectWithSuccess(
            $returnTo !== ''
                ? $returnTo
                : 'chinois/grammaire/hsk' . $level,
            'Grammaire modifiée.',
        );
    }

    public function updateVocabulaire(
        ChinoisVocabulaireCreateRequest $request,
        string $_langue,
        int $id,
    ): never {

        $this->vocabulaireOrFail(
            $id,
        );

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $dto =
            $request->dto();

        $this->chinoisWriteService
            ->updateVocabulaire(
                $id,
                $dto,
            );

        $returnTo =
            (string) $this->request
                ->input(
                    'return_to',
                    '',
                );

        $this->redirectWithSuccess(
            $returnTo !== ''
                ? $returnTo
                : 'chinois/vocabulaire/' . $dto->langue,
            'Vocabulaire modifié.',
        );
    }

    public function showGrammaire(
        int $id,
    ): never {

        $grammaire =
            $this->grammaireOrFail(
                $id,
            );

        $this->title =
            'Chinois | '
            . $grammaire->titre;

        $this->render(
            'pages/chinois/grammaire/recherche',
            [
                'grammaire' =>
                    $grammaire,
            ],
        );
    }

    public function showVocabulaire(
        int $id,
    ): never {

        $vocabulaire =
            $this->vocabulaireOrFail(
                $id,
            );

        $this->title =
            'Chinois | '
            . $vocabulaire->mot;

        $this->render(
            'pages/chinois/vocabulaire/recherche',
            [
                'vocabulaire' =>
                    $vocabulaire,
            ],
        );
    }
}