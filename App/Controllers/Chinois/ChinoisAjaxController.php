<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisWriteService;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Constants\UserXp;
use App\Services\User\UserLevelService;

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisGrammaireRepository $repository,
        private readonly ChinoisVocabulaireRepository $vocabulaireRepository,
        private readonly ChinoisWriteService $writeService,
        private readonly UserLevelService $userLevelService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function toggleGrammaireMaitrise(): never
    {
        $id =
            (int) $this->request->input(
                'id',
                0,
            );

        if ($id <= 0)
        {
            throw new ValidationException(
                [
                    'id' => 'ID invalide',
                ],
            );
        }

        $grammaire =
            $this->repository
                ->findById(
                    $id,
                );

        $maitrise =
            $this->repository
                ->toggleMaitrise(
                    $id,
                );

        if (
            $maitrise === 1
            && $grammaire !== null
            && $grammaire->xpRewarded === 0
        ) {
            $user = user();

            if ($user !== null)
            {
                $this->userLevelService
                    ->addXp(
                        $user,
                        UserXp::LEARN_GRAMMAR,
                    );
            }

            $this->repository
                ->markXpRewarded(
                    $id,
                );
        }

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $maitrise === 1
                        ? 'Grammaire maîtrisée'
                        : 'Grammaire non maîtrisée',

                data: [
                    'maitrise' =>
                        $maitrise,
                ],
            ),
        );
    }

    public function toggleVocabulaireMaitrise(): never
    {
        $id =
            (int) $this->request->input(
                'id',
                0,
            );

        if ($id <= 0)
        {
            throw new ValidationException(
                [
                    'id' => 'ID invalide',
                ],
            );
        }

        $vocabulaire =
            $this->vocabulaireRepository
                ->findById(
                    $id,
                );

        $maitrise =
            $this->vocabulaireRepository
                ->toggleMaitrise(
                    $id,
                );

        if (
            $maitrise === 1
            && $vocabulaire !== null
            && $vocabulaire->xp_rewarded === 0
        ) {
            $user = user();

            if ($user !== null)
            {
                $this->userLevelService
                    ->addXp(
                        $user,
                        UserXp::LEARN_VOCABULARY,
                    );
            }

            $this->vocabulaireRepository
                ->markXpRewarded(
                    $id,
                );
        }

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $maitrise === 1
                        ? 'Vocabulaire maîtrisé'
                        : 'Vocabulaire non maîtrisé',

                data: [
                    'maitrise' =>
                        $maitrise,
                ],
            ),
        );
    }

    public function deleteGrammaire(): never
    {
        $id =
            (int) $this->request->input(
                'id',
                0,
            );

        if ($id <= 0)
        {
            throw new ValidationException(
                [
                    'id' =>
                        'ID invalide',
                ],
            );
        }

        $result =
            $this->writeService
                ->deleteGrammaire(
                    $id,
                );

        $this->jsonResult(
            $result,
        );
    }

    public function deleteVocabulaire(): never
    {
        $id =
            (int) $this->request->input(
                'id',
                0,
            );

        if ($id <= 0)
        {
            throw new ValidationException(
                [
                    'id' =>
                        'ID invalide',
                ],
            );
        }

        $result =
            $this->writeService
                ->deleteVocabulaire(
                    $id,
                );

        $this->jsonResult(
            $result,
        );
    }
}