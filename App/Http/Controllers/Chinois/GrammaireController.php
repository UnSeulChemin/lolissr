<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chinois;

use App\DTO\Chinois\Responses\ChinoisGrammaireData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Http\Exceptions\BaseHttpException;
use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Requests\Request;

final class GrammaireController extends Controller
{
    private const HSK_LEVELS = [1, 2, 3, 4];

    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisWriteService $chinoisWriteService,
        Request $request
    )
    {
        parent::__construct($request);
    }

    // =================================================
    // PAGES
    // =================================================

    public function index(): never
    {
        $this->title = 'Chinois | Grammaires';

        $this->render('pages/chinois/grammaire/index');
    }

    public function hsk(int $level, ?string $section = null): never
    {
        $hskLevel = $this->resolveHskLevel($level);

        $this->title = 'Chinois | Grammaires ' . $hskLevel;

        $this->render('pages/chinois/grammaire/hsk', [
            'hsk' => $this->chinoisReadService->hsk($hskLevel, $section ?? $this->stringInput('section'))
        ]);
    }

    public function show(string $niveau, int $id): never
    {
        $grammaire = $this->grammaireOrFail($niveau, $id);

        $this->title = 'Chinois | ' . $grammaire->titre;

        $this->render('pages/chinois/grammaire/show', ['grammaire' => $grammaire]);
    }

    // =================================================
    // CRÉATION
    // =================================================

    public function create(): never
    {
        $this->title = 'Chinois | Ajouter une grammaire';

        $this->render('pages/chinois/grammaire/create', [
            'form' => $this->formViewData('chinois/ajouter/grammaire', 'chinois/ajouter')
        ]);
    }

    public function store(ChinoisGrammaireCreateRequest $request): never
    {
        $this->validateRequest($request);

        $this->jsonResult($this->chinoisWriteService->createGrammaire($request->dto()));
    }

    // =================================================
    // MODIFICATION
    // =================================================

    public function edit(int $level, int $id): never
    {
        $niveau = $this->resolveHskLevel($level);

        $this->renderEdit($niveau, $id, $this->returnPathInput());
    }

    public function update(ChinoisGrammaireCreateRequest $request, int $level, int $id): never
    {
        $niveau = $this->resolveHskLevel($level);

        $this->grammaireOrFail($niveau, $id);
        $this->validateRequest($request);

        $returnTo = $this->returnPathInput();
        $dto = $request->dto();
        $result = $this->chinoisWriteService->updateGrammaire($id, $dto);

        if (! $result->success)
        {
            throw new BaseHttpException(message: $result->message, statusCode: $result->status, data: $result->data);
        }

        $destination = 'chinois/grammaire/' . mb_strtolower($dto->niveau);

        $this->redirectWithSuccess($returnTo !== '' ? $returnTo : $destination, $result->message);
    }

    // =================================================
    // RÉSOLUTION
    // =================================================

    private function resolveHskLevel(int $level): string
    {
        if (! in_array($level, self::HSK_LEVELS, true))
        {
            throw new NotFoundException('Niveau HSK introuvable');
        }

        return 'HSK' . $level;
    }

    private function grammaireOrFail(string $niveau, int $id): ChinoisGrammaireData
    {
        return $this->chinoisReadService->grammaire($niveau, $id)
            ?? throw new NotFoundException('Grammaire introuvable');
    }

    // =================================================
    // RENDU
    // =================================================

    private function renderEdit(string $niveau, int $id, string $returnTo): never
    {
        $grammaire = $this->grammaireOrFail($niveau, $id);
        $hskLevel = substr($grammaire->niveau, 3);

        $this->title = 'Chinois | Modifier une grammaire';

        $this->render('pages/chinois/grammaire/edit', [
            'grammaire' => $grammaire,
            'returnTo' => $returnTo,
            'form' => $this->formViewData(
                sprintf('chinois/grammaire/hsk%s/modifier/%d', $hskLevel, $grammaire->id),
                $returnTo !== ''
                    ? $returnTo
                    : 'chinois/grammaire/hsk' . $hskLevel
            )
        ]);
    }
}
