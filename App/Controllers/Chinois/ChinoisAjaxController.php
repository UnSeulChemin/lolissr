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

    public function toggleGrammaireMaitrise(): never
    {
        // Correction :
        // suppression du gros commentaire de section.
        // Le nom de la méthode explique déjà parfaitement le rôle.

        // Très bon réflexe déjà présent :
        // protection AJAX stricte.
        if (!$this->isAjax()) {
            $this->json([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $id = $this->request->integer('id');

        // Très bon contrôle ici :
        // évite les IDs invalides avant accès DB.
        if ($id <= 0) {
            $this->json([
                'success' => false,
                'message' => 'ID invalide',
            ], 422);
        }

        $maitrise = $this->repository
            ->toggleMaitrise($id);

        // Très bonne réponse API :
        // simple, cohérente, minimale.
        $this->json([
            'success' => true,
            'maitrise' => $maitrise,
        ]);
    }
}