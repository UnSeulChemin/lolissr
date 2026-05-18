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

    public function toggleGrammaireMaitrise(Request $request): void
    {
        $id = $request->integer('id');

        if ($id <= 0) {
            json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);

            return;
        }

        json([
            'success' => true,
            'maitrise' => $this->repository->toggleMaitrise($id),
        ]);
    }
}