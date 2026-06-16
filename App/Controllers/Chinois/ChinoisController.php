<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Http\Requests\Chinois\ChinoisVocabulaireCreateRequest;
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

    public function mandarin(): never
    {
        $this->title = 'Chinois | Mandarin';
        $this->render('pages/chinois/vocabulaire/index', [
            'langue' => 'mandarin',
            'vocabulaires' => $this->chinoisReadService->mandarin(),
        ]);
    }

    public function jinyu(): never
    {
        $this->title = 'Chinois | 晋语';
        $this->render('pages/chinois/vocabulaire/index', [
            'langue' => '晋语',
            'vocabulaires' => $this->chinoisReadService->jinyu(),
        ]);
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
        int $id,
    ): never {

        $grammaire =
            $this->chinoisReadService
                ->grammaire($id)
            ?? throw new NotFoundException(
                'Grammaire introuvable',
            );

        $this->title =
            'Chinois | Modifier une grammaire';

        $this->render(
            'pages/chinois/grammaire/modifier',
            [
                'grammaire' => $grammaire,

                'returnTo' =>
                    (string) $this->request
                        ->input(
                            'return_to',
                            '',
                        ),
            ],
        );
    }

    public function editVocabulaire(
        int $id,
    ): never {

        $vocabulaire =
            $this->chinoisReadService
                ->vocabulaire($id)
            ?? throw new NotFoundException(
                'Vocabulaire introuvable',
            );

        $this->title =
            'Chinois | Modifier du vocabulaire';

        $this->render(
            'pages/chinois/vocabulaire/modifier',
            [
                'vocabulaire' => $vocabulaire,

                'returnTo' =>
                    (string) $this->request
                        ->input(
                            'return_to',
                            '',
                        ),
            ],
        );
    }

    public function updateGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $id,
    ): never {

        $this->chinoisReadService
            ->grammaire($id)
            ?? throw new NotFoundException(
                'Grammaire introuvable',
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
        int $id,
    ): never {

        $this->chinoisReadService
            ->vocabulaire($id)
            ?? throw new NotFoundException(
                'Vocabulaire introuvable',
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
                : 'chinois/' . $dto->langue,
            'Vocabulaire modifié.',
        );
    }
}