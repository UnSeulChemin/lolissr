<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
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
    |--------------------------------------------------------------------------
    | Toggle maîtrise grammaire
    |--------------------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(): never
    {
        if (!$this->isAjax()) {
            $this->json([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $id = $this->request->integer('id');

        if ($id <= 0) {
            $this->json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);
        }

        $maitrise = $this->repository
            ->toggleMaitrise($id);

        $this->json([
            'success' => true,
            'maitrise' => $maitrise,
        ]);
    }
}
