<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Core\Http\Request;
use App\Repositories\Chinois\ChinoisGrammaireRepository;

final class ChinoisAjaxController
{
    public function __construct(
        private readonly ChinoisGrammaireRepository $repository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle maîtrise grammaire
    |--------------------------------------------------------------------------
    */

    public function toggleGrammaireMaitrise(Request $request): void
    {
        $id = $request->integer('id');

        /*
        |--------------------------------------------------------------------------
        | Vérifie l'ID
        |--------------------------------------------------------------------------
        */

        if ($id <= 0)
        {
            json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);

            return;
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

        json([
            'success' => true,
            'maitrise' => $maitrise,
        ]);
    }
}