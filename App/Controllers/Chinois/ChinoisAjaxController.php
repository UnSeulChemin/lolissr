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
    ) {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle maîtrise grammaire
    |--------------------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(
        Request $request,
    ): never {
        if (!$this->isAjax($request)) {
            $this->json([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $id = $request->integer('id');

        /*
        |--------------------------------------------------------------------------
        | Vérifie l'ID
        |--------------------------------------------------------------------------
        */

        if ($id <= 0) {
            $this->json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Toggle maîtrise
        |--------------------------------------------------------------------------
        */

        $maitrise = $this->repository
            ->toggleMaitrise($id);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $this->json([
            'success' => true,
            'maitrise' => $maitrise,
        ]);
    }
}