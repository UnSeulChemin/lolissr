<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisGrammaireRepository $repository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------
    | Toggle grammaire maîtrise
    |--------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(): never
    {
        $this->ensureAjax();

        $id =
            (int) $this->request
                ->input(
                    'id',
                    0,
                );

        if ($id <= 0) {

            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $maitrise =
            (int) $this->repository
                ->toggleMaitrise($id);

        $message =
            $maitrise === 1
                ? 'Grammaire marquée comme maîtrisée'
                : 'Grammaire marquée comme non maîtrisée';

        $result =
            ServiceResult::success(
                message: $message,

                data: [
                    'maitrise' => $maitrise,
                ],
            );

        $this->json($result);
    }

    /*
    |--------------------------------------------------------------
    | Ensure AJAX request
    |--------------------------------------------------------------
    */

    private function ensureAjax(): void
    {
        if (
            $this->isAjax()
            || App::isTesting()
        ) {
            return;
        }

        throw new BaseHttpException(
            message:
                'Requête AJAX requise',

            statusCode:
                400,
        );
    }
}