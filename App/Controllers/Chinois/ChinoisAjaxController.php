<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
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
        $result = $this->chinoisWriteService
            ->toggleGrammaireMaitrise(
                $this->getIdOrFail(),
            );

        $user = user();

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $result['maitrise']
                        ? 'Grammaire maîtrisée'
                        : 'Grammaire non maîtrisée',
                data: [
                    'maitrise' => $result['maitrise'],
                    'xpEarned' => $result['xpEarned'],
                    'level' => $user?->level,
                    'xp' => $user?->xp,
                ],
            ),
        );
    }

    public function toggleVocabulaireMaitrise(): never
    {
        $result = $this->chinoisWriteService
            ->toggleVocabulaireMaitrise(
                $this->getIdOrFail(),
            );

        $user = user();

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $result['maitrise']
                        ? 'Vocabulaire maîtrisé'
                        : 'Vocabulaire non maîtrisé',
                data: [
                    'maitrise' => $result['maitrise'],
                    'xpEarned' => $result['xpEarned'],
                    'level' => $user?->level,
                    'xp' => $user?->xp,
                ],
            ),
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
}
