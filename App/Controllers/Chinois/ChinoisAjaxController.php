<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Repositories\Chinois\ChinoisGrammaireRepository;

final class ChinoisAjaxController
{
    public function __construct(
        private ChinoisGrammaireRepository $repository
    ) {
    }

    public function toggleGrammaireMaitrise(): void
    {
        header('Content-Type: application/json');

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0)
        {
            echo json_encode([
                'success' => false,
                'message' => 'ID invalide'
            ]);

            return;
        }

        $maitrise = $this->repository->toggleMaitrise($id);

        echo json_encode([
            'success' => true,
            'maitrise' => $maitrise,
            'message' => $maitrise
                ? 'Grammaire maîtrisée'
                : 'Maîtrise retirée'
        ]);
    }
}