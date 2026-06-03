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

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisGrammaireRepository $repository,
        private readonly ChinoisVocabulaireRepository $vocabulaireRepository,
        private readonly ChinoisWriteService $writeService,
        Request $request,
    ) {
        parent::__construct(
            $request,
        );
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
                    'id' =>
                        'ID invalide',
                ],
            );
        }

        $maitrise =
            $this->repository
                ->toggleMaitrise(
                    $id,
                );

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
                    'id' =>
                        'ID invalide',
                ],
            );
        }

        $maitrise =
            $this->vocabulaireRepository
                ->toggleMaitrise(
                    $id,
                );

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
}