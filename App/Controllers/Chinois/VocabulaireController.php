<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Chinois\Responses\ChinoisVocabulaireData;
use App\Http\Requests\Chinois\ChinoisVocabulaireCreateRequest;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class VocabulaireController extends Controller
{
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

        $languageTitle = $langue === 'jinyu' ? '晋语' : 'Mandarin';
        $pageTitle = $data->currentPage > 1 ? ' - Page ' . $data->currentPage : '';

        $this->title = 'Chinois | ' . $languageTitle . $pageTitle;

        $this->render('pages/chinois/vocabulaire/langue', [
            'langue' => $langue,
            'vocabulaires' => $data->vocabulaires,
            'currentPage' => $data->currentPage,
            'totalVocabulaires' => $data->totalVocabulaires,
            'perPage' => $data->perPage,
            'totalPages' => $data->totalPages,
        ]);
    }

    public function show(string $langue, int $id): never
    {
        $vocabulaire = $this->vocabulaireOrFail($langue, $id);

        $this->title = 'Chinois | ' . $vocabulaire->mot;

        $this->render('pages/chinois/vocabulaire/recherche', [
            'vocabulaire' => $vocabulaire,
        ]);
    }

    // =========================================
    // CRÉATION
    // =========================================

    public function create(): never
    {
        $this->title = 'Chinois | Ajouter du vocabulaire';

        $this->render('pages/chinois/ajouter/vocabulaire', [
            'form' => $this->formViewData(
                'chinois/ajouter/vocabulaire',
                'chinois/ajouter'
            ),
        ]);
    }

    public function store(ChinoisVocabulaireCreateRequest $request): never
    {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->chinoisWriteService->createVocabulaire($request->dto())
        );
    }

    // =========================================
    // MODIFICATION
    // =========================================

    public function edit(string $langue, int $id): never
    {
        $this->renderEdit(
            $langue,
            $id,
            (string) $this->request->input('return_to', '')
        );
    }

    public function update(
        ChinoisVocabulaireCreateRequest $request,
        string $langue,
        int $id
    ): never {
        $this->vocabulaireOrFail($langue, $id);
        $this->validateRequest($request);

        $dto = $request->dto();
        $result = $this->chinoisWriteService->updateVocabulaire($id, $dto);

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
            $returnTo !== '' ? $returnTo : 'chinois/vocabulaire/' . $dto->langue,
            $result->message
        );
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private function vocabulaireOrFail(string $langue, int $id): ChinoisVocabulaireData
    {
        return $this->chinoisReadService->vocabulaire($langue, $id)
            ?? throw new NotFoundException('Vocabulaire introuvable');
    }

    // =========================================
    // RENDU
    // =========================================

    private function renderEdit(string $langue, int $id, string $returnTo): never
    {
        $vocabulaire = $this->vocabulaireOrFail($langue, $id);

        $this->title = 'Chinois | Modifier du vocabulaire';

        $this->render('pages/chinois/vocabulaire/modifier', [
            'vocabulaire' => $vocabulaire,
            'returnTo' => $returnTo,
            'form' => $this->formViewData(
                sprintf(
                    'chinois/vocabulaire/%s/modifier/%d',
                    $vocabulaire->langue,
                    $vocabulaire->id
                ),
                $returnTo !== ''
                    ? $returnTo
                    : 'chinois/vocabulaire/' . $vocabulaire->langue
            ),
        ]);
    }
}