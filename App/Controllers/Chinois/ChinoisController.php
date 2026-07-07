<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Http\Requests\Chinois\ChinoisVocabulaireCreateRequest;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\FormRequest;
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
    | PAGES PRINCIPALES
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

    public function langue(string $langue, int $page = 1): never
    {
        $langue = mb_strtolower($langue);

        $data = $this->chinoisReadService->langue($langue, $page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Chinois | '
            . ($langue === 'jinyu' ? '晋语' : 'Mandarin')
            . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render('pages/chinois/vocabulaire/langue', [
            'langue' => $langue,
            'vocabulaires' => $data->vocabulaires,
            'currentPage' => $data->currentPage,
            'totalVocabulaires' => $data->totalVocabulaires,
            'perPage' => $data->perPage,
            'totalPages' => $data->totalPages,
        ]);
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Grammaire';

        $this->render('pages/chinois/grammaire/index');
    }

    public function hsk(int $level): never
    {
        $hskLevel = $this->resolveHskLevel($level);

        $this->title = 'Chinois | Grammaire ' . $hskLevel;

        $this->render('pages/chinois/grammaire/hsk', [
            'hsk' => $this->chinoisReadService->hsk($hskLevel),
        ]);
    }

    public function flashcards(): never
    {
        $this->title = 'Chinois | Flashcards';

        $this->render('pages/chinois/flashcards/index');
    }

    public function flashcardsVocabulaire(): never
    {
        $this->title = 'Chinois | Flashcards Vocabulaire';

        $this->render('pages/chinois/flashcards/vocabulaire', [
            'vocabulaires' => $this->chinoisReadService->flashcardsVocabulaire(),
        ]);
    }

    public function flashcardsGrammaire(): never
    {
        $this->title = 'Chinois | Flashcards Grammaire';

        $this->render('pages/chinois/flashcards/grammaire', [
            'grammaires' => $this->chinoisReadService->flashcardsGrammaire(),
        ]);
    }

    public function ajouter(): never
    {
        $this->title = 'Chinois | Ajouter';

        $this->render('pages/chinois/ajouter/index');
    }

    public function createGrammaire(): never
    {
        $this->title = 'Chinois | Ajouter une grammaire';

        $this->render('pages/chinois/ajouter/grammaire', [
            'form' => $this->formViewData(
                'chinois/ajouter/grammaire',
                'chinois/ajouter',
            ),
        ]);
    }

    public function createVocabulaire(): never
    {
        $this->title = 'Chinois | Ajouter du vocabulaire';

        $this->render('pages/chinois/ajouter/vocabulaire', [
            'form' => $this->formViewData(
                'chinois/ajouter/vocabulaire',
                'chinois/ajouter',
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FLASHCARDS
    |--------------------------------------------------------------------------
    */

    public function editFlashcardVocabulaire(int $id): never
    {
        $this->renderEditVocabulaire($id, 'chinois/flashcards/vocabulaire');
    }

    public function updateFlashcardVocabulaire(
        ChinoisVocabulaireCreateRequest $request,
        int $id
    ): never
    {
        $this->vocabulaireOrFail($id);

        $this->validateRequest($request);

        $result = $this->chinoisWriteService->updateVocabulaire(
            $id,
            $request->dto(),
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data,
            );
        }

        $this->redirectWithSuccess(
            'chinois/flashcards/vocabulaire',
            $result->message,
        );
    }

    public function editFlashcardGrammaire(int $id): never
    {
        $this->renderEditGrammaire($id, 'chinois/flashcards/grammaire');
    }

    public function updateFlashcardGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $id
    ): never
    {
        $this->grammaireOrFail($id);

        $this->validateRequest($request);

        $result = $this->chinoisWriteService->updateGrammaire(
            $id,
            $request->dto(),
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data,
            );
        }

        $this->redirectWithSuccess(
            'chinois/flashcards/grammaire',
            $result->message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CRÉATION
    |--------------------------------------------------------------------------
    */

    public function storeGrammaire(
        ChinoisGrammaireCreateRequest $request
    ): never
    {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->chinoisWriteService->createGrammaire(
                $request->dto(),
            ),
        );
    }

    public function storeVocabulaire(
        ChinoisVocabulaireCreateRequest $request
    ): never
    {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->chinoisWriteService->createVocabulaire(
                $request->dto(),
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ÉDITION
    |--------------------------------------------------------------------------
    */

    public function editGrammaire(int $_level, int $id): never
    {
        $this->renderEditGrammaire(
            $id,
            (string) $this->request->input('return_to', ''),
        );
    }

    public function editVocabulaire(string $_langue, int $id): never
    {
        $this->renderEditVocabulaire(
            $id,
            (string) $this->request->input('return_to', ''),
        );
    }

    public function updateGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $_level,
        int $id
    ): never
    {
        $this->grammaireOrFail($id);

        $this->validateRequest($request);

        $dto = $request->dto();

        $result = $this->chinoisWriteService->updateGrammaire(
            $id,
            $dto,
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data,
            );
        }

        $level = substr($dto->niveau, 3);

        $returnTo = (string) $this->request->input('return_to', '');

        $this->redirectWithSuccess(
            $returnTo !== ''
                ? $returnTo
                : 'chinois/grammaire/hsk' . $level,
            $result->message,
        );
    }

    public function updateVocabulaire(
        ChinoisVocabulaireCreateRequest $request,
        string $_langue,
        int $id
    ): never
    {
        $this->vocabulaireOrFail($id);

        $this->validateRequest($request);

        $dto = $request->dto();

        $result = $this->chinoisWriteService->updateVocabulaire(
            $id,
            $dto,
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data,
            );
        }

        $returnTo = (string) $this->request->input('return_to', '');

        $this->redirectWithSuccess(
            $returnTo !== ''
                ? $returnTo
                : 'chinois/vocabulaire/' . $dto->langue,
            $result->message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTATION
    |--------------------------------------------------------------------------
    */

    public function showGrammaire(string $_niveau, int $id): never
    {
        $grammaire = $this->grammaireOrFail($id);

        $this->title = 'Chinois | ' . $grammaire->titre;

        $this->render('pages/chinois/grammaire/recherche', [
            'grammaire' => $grammaire,
        ]);
    }

    public function showVocabulaire(string $_langue, int $id): never
    {
        $vocabulaire = $this->vocabulaireOrFail($id);

        $this->title = 'Chinois | ' . $vocabulaire->mot;

        $this->render('pages/chinois/vocabulaire/recherche', [
            'vocabulaire' => $vocabulaire,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function resolveHskLevel(int $level): string
    {
        if (! in_array($level, self::HSK_LEVELS, true))
        {
            throw new NotFoundException(
                'Niveau HSK introuvable'
            );
        }

        return "HSK{$level}";
    }

    private function validateRequest(FormRequest $request): void
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }
    }

    private function vocabulaireOrFail(int $id): ChinoisVocabulaireData
    {
        return $this->chinoisReadService->vocabulaire($id)
            ?? throw new NotFoundException('Vocabulaire introuvable');
    }

    private function grammaireOrFail(int $id): ChinoisGrammaireData
    {
        return $this->chinoisReadService->grammaire($id)
            ?? throw new NotFoundException('Grammaire introuvable');
    }

    private function renderEditVocabulaire(int $id, string $returnTo): never
    {
        $vocabulaire = $this->vocabulaireOrFail($id);

        $this->title = 'Chinois | Modifier du vocabulaire';

        $this->render('pages/chinois/vocabulaire/modifier', [
            'vocabulaire' => $vocabulaire,
            'returnTo' => $returnTo,
            'form' => $this->formViewData(
                sprintf(
                    'chinois/vocabulaire/%s/modifier/%d',
                    $vocabulaire->langue,
                    $vocabulaire->id,
                ),
                $returnTo !== ''
                    ? $returnTo
                    : 'chinois/vocabulaire/' . $vocabulaire->langue,
            ),
        ]);
    }

    private function renderEditGrammaire(int $id, string $returnTo): never
    {
        $grammaire = $this->grammaireOrFail($id);

        $this->title = 'Chinois | Modifier une grammaire';

        $this->render('pages/chinois/grammaire/modifier', [
            'grammaire' => $grammaire,
            'returnTo' => $returnTo,
            'form' => $this->formViewData(
                sprintf(
                    'chinois/grammaire/%s/modifier/%d',
                    strtolower($grammaire->niveau),
                    $grammaire->id,
                ),
                $returnTo !== ''
                    ? $returnTo
                    : 'chinois/grammaire/hsk' . substr($grammaire->niveau, 3),
            ),
        ]);
    }
}