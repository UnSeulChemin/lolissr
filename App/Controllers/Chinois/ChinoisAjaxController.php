<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class ChinoisAjaxController extends Controller
{
    public function __construct(
        private readonly ChinoisGrammaireRepository $repository,
        Request $request
    ) {
        parent::__construct($request); // <- Obligatoire pour ton Controller parent
    }

    /**
     * Toggle la maîtrise d'une règle de grammaire.
     * Répond uniquement en AJAX.
     */
    public function toggleGrammaireMaitrise(): never
    {
        $this->ensureAjax();

        // Récupère l'ID depuis POST via le tableau global
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $maitrise = $this->repository->toggleMaitrise($id);

        $this->json([
            'success' => true,
            'maitrise' => $maitrise,
            'message' => 'Grammaire marquée comme maîtrisée'
        ]);
    }

    private function ensureAjax(): void
    {
        if ($this->isAjax() || \Framework\Application\App::isTesting()) {
            return;
        }

        throw new BaseHttpException(
            message: 'Requête AJAX requise',
            statusCode: 400
        );
    }
}