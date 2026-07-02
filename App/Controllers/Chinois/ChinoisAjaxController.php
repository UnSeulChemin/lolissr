<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Constants\UserXp;
use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;
use App\Services\User\UserLevelService;

use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisWriteService $chinoisWriteService,
        private readonly UserLevelService $userLevelService,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): never
    {
        $searchData = $this->chinoisReadService->search((string) $query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MAÎTRISE
    |--------------------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(): never
    {
        $id = $this->getIdOrFail();

        $grammaire = $this->chinoisReadService->grammaire($id);

        $maitrise = $this->chinoisWriteService->toggleGrammaireMaitrise($id);

        if ($maitrise && $grammaire !== null && ! $grammaire->xpRewarded)
        {
            $this->rewardGrammarXp($id);
        }

        $this->jsonResult(ServiceResult::success(
                message:
                    $maitrise
                        ? 'Grammaire maîtrisée'
                        : 'Grammaire non maîtrisée',
                data:
                    ['maitrise' => $maitrise]
            )
        );
    }

    public function toggleVocabulaireMaitrise(): never
    {
        $id = $this->getIdOrFail();

        $vocabulaire = $this->chinoisReadService->vocabulaire($id);

        $maitrise = $this->chinoisWriteService->toggleVocabulaireMaitrise($id);

        if ($maitrise && $vocabulaire !== null && ! $vocabulaire->xpRewarded)
        {
            $this->rewardVocabularyXp($id);
        }

        $this->jsonResult(ServiceResult::success(
                message:
                    $maitrise
                        ? 'Vocabulaire maîtrisé'
                        : 'Vocabulaire non maîtrisé',
                data:
                    ['maitrise' => $maitrise]
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION
    |--------------------------------------------------------------------------
    */

    public function deleteGrammaire(): never
    {
        $this->jsonResult($this->chinoisWriteService->deleteGrammaire($this->getIdOrFail()));
    }

    public function deleteVocabulaire(): never
    {
        $this->jsonResult($this->chinoisWriteService->deleteVocabulaire($this->getIdOrFail()));
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function getIdOrFail(): int
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0)
        {
            throw new ValidationException(['id' => 'ID invalide']);
        }

        return $id;
    }

    private function rewardGrammarXp(int $id): void
    {
        $user = user();

        if ($user !== null)
        {
            $this->userLevelService->addXp($user, UserXp::LEARN_GRAMMAR);
        }

        $this->chinoisWriteService->markGrammaireXpRewarded($id);
    }

    private function rewardVocabularyXp(int $id): void
    {
        $user = user();

        if ($user !== null)
        {
            $this->userLevelService->addXp($user, UserXp::LEARN_VOCABULARY);
        }

        $this->chinoisWriteService->markVocabulaireXpRewarded($id);
    }
}
