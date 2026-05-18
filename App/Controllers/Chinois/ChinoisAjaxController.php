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

    public function toggleGrammaireMaitrise(
        Request $request
    ): void {
        $id = (int) $request->input('id', 0);

        if ($id <= 0) {
            json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);
        }

        $maitrise = $this->repository->toggleMaitrise($id);

        json([
            'success' => true,
            'maitrise' => $maitrise,
            'message' => $maitrise
                ? 'Grammaire maîtrisée'
                : 'Maîtrise retirée',
        ]);
    }
}