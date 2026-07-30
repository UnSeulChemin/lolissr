<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class GrammaireController extends Controller
{
    private const HSK_LEVELS = [1, 2, 3, 4];

    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisWriteService $chinoisWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // PAGES
    // =========================================

    public function index(): never
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

    public function show(string $niveau, int $id): never
    {
        $grammaire = $this->grammaireOrFail($id);

        $this->title = 'Chinois | ' . $grammaire->titre;

        $this->render('pages/chinois/grammaire/recherche', [
            'grammaire' => $grammaire,
        ]);
    }

    // =========================================
    // CRÉATION
    // =========================================

    public function create(): never
    {
        $this->title = 'Chinois | Ajouter une grammaire';

        $this->render('pages/chinois/ajouter/grammaire', [
            'form' => $this->formViewData(
                'chinois/ajouter/grammaire',
                'chinois/ajouter'
            ),
        ]);
    }

    public function store(ChinoisGrammaireCreateRequest $request): never
    {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->chinoisWriteService->createGrammaire($request->dto())
        );
    }

    // =========================================
    // MODIFICATION
    // =========================================

    public function edit(int $level, int $id): never
    {
        $this->renderEdit(
            $id,
            (string) $this->request->input('return_to', '')
        );
    }

    public function update(
        ChinoisGrammaireCreateRequest $request,
        int $level,
        int $id
    ): never {
        $this->grammaireOrFail($id);
        $this->validateRequest($request);

        $result = $this->chinoisWriteService->updateGrammaire(
            $id,
            $request->dto()
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data
            );
        }

        $returnTo = (string) $this->request->input('return_to', '');

        $this->redirectWithSuccess(
            $returnTo !== '' ? $returnTo : 'chinois/grammaire/hsk' . $level,
            $result->message
        );
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private function resolveHskLevel(int $level): string
    {
        if (! in_array($level, self::HSK_LEVELS, true))
        {
            throw new NotFoundException('Niveau HSK introuvable');
        }

        return 'HSK' . $level;
    }

    private function grammaireOrFail(int $id): ChinoisGrammaireData
    {
        return $this->chinoisReadService->grammaire($id)
            ?? throw new NotFoundException('Grammaire introuvable');
    }

    // =========================================
    // RENDU
    // =========================================

    private function renderEdit(int $id, string $returnTo): never
    {
        $grammaire = $this->grammaireOrFail($id);
        $hskLevel = substr($grammaire->niveau, 3);

        $this->title = 'Chinois | Modifier une grammaire';

        $this->render('pages/chinois/grammaire/modifier', [
            'grammaire' => $grammaire,
            'returnTo' => $returnTo,
            'form' => $this->formViewData(
                sprintf(
                    'chinois/grammaire/hsk%s/modifier/%d',
                    $hskLevel,
                    $grammaire->id
                ),
                $returnTo !== ''
                    ? $returnTo
                    : 'chinois/grammaire/hsk' . $hskLevel
            ),
        ]);
    }
}