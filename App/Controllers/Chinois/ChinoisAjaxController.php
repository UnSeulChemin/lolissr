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
    private const AJAX_PATH = 'chinois/ajax';

    public function __construct(
        private readonly ChinoisGrammaireRepository $repository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    /**
     * Toggle la maîtrise d'une règle de grammaire.
     * Répond uniquement en AJAX.
     */
    public function toggleGrammaireMaitrise(): never
    {
        $this->ensureAjax();

        $id = $this->request->integer('id');

        if ($id <= 0) {
            throw new ValidationException([
                'id' => 'ID invalide',
            ]);
        }

        $maitrise = $this->repository->toggleMaitrise($id);

        // Retourne exactement ce que le JS attend
        $this->json([
            'success' => true,
            'maitrise' => $maitrise,
        ]);
    }

    /**
     * Vérifie que la requête est bien AJAX ou test.
     */
    private function ensureAjax(): void
    {
        if ($this->isAjax() || \Framework\Application\App::isTesting()) {
            return;
        }

        throw new BaseHttpException(
            message: 'Requête AJAX requise',
            statusCode: 400,
        );
    }
}