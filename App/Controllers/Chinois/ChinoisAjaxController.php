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
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Search
    |--------------------------------------------------------------------------
    */

    public function search(
        string $query = '',
    ): never
    {
        $searchData =
            $this->chinoisReadService
                ->search($query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' =>
                        $searchData->results,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Maîtrise
    |--------------------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(): never
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0)
        {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $grammaire =
            $this->chinoisReadService
                ->grammaire($id);

        $maitrise =
            $this->chinoisWriteService
                ->toggleGrammaireMaitrise($id);

        if (
            $maitrise
            && $grammaire !== null
            && ! $grammaire->xpRewarded
        )
        {
            $user = user();

            if ($user !== null)
            {
                $this->userLevelService->addXp(
                    $user,
                    UserXp::LEARN_GRAMMAR,
                );
            }

            $this->chinoisWriteService
                ->markGrammaireXpRewarded($id);
        }

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $maitrise
                        ? 'Grammaire maîtrisée'
                        : 'Grammaire non maîtrisée',

                data: [
                    'maitrise' => $maitrise,
                ],
            ),
        );
    }

    public function toggleVocabulaireMaitrise(): never
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0)
        {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $vocabulaire =
            $this->chinoisReadService
                ->vocabulaire($id);

        $maitrise =
            $this->chinoisWriteService
                ->toggleVocabulaireMaitrise($id);

        if (
            $maitrise
            && $vocabulaire !== null
            && ! $vocabulaire->xpRewarded
        )
        {
            $user = user();

            if ($user !== null)
            {
                $this->userLevelService->addXp(
                    $user,
                    UserXp::LEARN_VOCABULARY,
                );
            }

            $this->chinoisWriteService
                ->markVocabulaireXpRewarded($id);
        }

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $maitrise
                        ? 'Vocabulaire maîtrisé'
                        : 'Vocabulaire non maîtrisé',

                data: [
                    'maitrise' => $maitrise,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Suppression
    |--------------------------------------------------------------------------
    */

    public function deleteGrammaire(): never
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0) {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $this->jsonResult(
            $this->chinoisWriteService->deleteGrammaire($id),
        );
    }

    public function deleteVocabulaire(): never
    {
        $id = (int) $this->request->input('id', 0);

        if ($id <= 0) {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $this->jsonResult(
            $this->chinoisWriteService->deleteVocabulaire($id),
        );
    }
}