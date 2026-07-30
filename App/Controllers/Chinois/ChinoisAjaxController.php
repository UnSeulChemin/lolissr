<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Chinois\Responses\ChinoisMaitriseData;
use App\DTO\Common\ServiceResult;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;

use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisWriteService $chinoisWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // RECHERCHE
    // =========================================

    public function search(string|int $query = ''): never
    {
        $searchData = $this->chinoisReadService->search((string) $query);

        $this->jsonResult(ServiceResult::success(data: [
            'results' => $searchData->results,
        ]));
    }

    // =========================================
    // MAÎTRISE
    // =========================================

    public function toggleGrammaireMaitrise(): never
    {
        $result = $this->chinoisWriteService->toggleGrammaireMaitrise($this->getIdOrFail());

        $this->jsonMaitriseResult(
            $result,
            'Grammaire maîtrisée',
            'Grammaire non maîtrisée'
        );
    }

    public function toggleVocabulaireMaitrise(): never
    {
        $result = $this->chinoisWriteService->toggleVocabulaireMaitrise($this->getIdOrFail());

        $this->jsonMaitriseResult(
            $result,
            'Vocabulaire maîtrisé',
            'Vocabulaire non maîtrisé'
        );
    }

    // =========================================
    // SUPPRESSION
    // =========================================

    public function deleteGrammaire(): never
    {
        $this->jsonResult($this->chinoisWriteService->deleteGrammaire($this->getIdOrFail()));
    }

    public function deleteVocabulaire(): never
    {
        $this->jsonResult($this->chinoisWriteService->deleteVocabulaire($this->getIdOrFail()));
    }

    // =========================================
    // RÉPONSES
    // =========================================

    private function jsonMaitriseResult(
        ChinoisMaitriseData $result,
        string $enabledMessage,
        string $disabledMessage
    ): never {
        $user = user();

        $this->jsonResult(ServiceResult::success(
            message: $result->maitrise ? $enabledMessage : $disabledMessage,
            data: [
                'maitrise' => $result->maitrise,
                'xpEarned' => $result->xpEarned,
                'level' => $user?->level,
                'xp' => $user?->xp,
            ]
        ));
    }

    // =========================================
    // VALIDATION
    // =========================================

    private function getIdOrFail(): int
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0)
        {
            throw new ValidationException(['id' => 'ID invalide']);
        }

        return $id;
    }
}