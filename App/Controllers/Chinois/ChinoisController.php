<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Core\Http\Request;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;

final class ChinoisController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository
    ) {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Accueil
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): never
    {
        $this->title = 'Chinois';

        $this->render('chinois/index');
    }

    /*
    |--------------------------------------------------------------------------
    | Mandarin
    |--------------------------------------------------------------------------
    */

    public function mandarin(Request $request): never
    {
        $this->title = 'Chinois | Mandarin';

        $this->render('chinois/mandarin', [
            'vocabulaires' => $this->chinoisReadService->mandarin(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 晋语
    |--------------------------------------------------------------------------
    */

    public function jinyu(Request $request): never
    {
        $this->title = 'Chinois | 晋语';

        $this->render('chinois/jinyu', [
            'vocabulaires' => $this->chinoisReadService->jinyu(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Grammaire
    |--------------------------------------------------------------------------
    */

    public function grammaire(Request $request): never
    {
        $this->title = 'Chinois | Grammaire';

        $this->render('chinois/grammaire');
    }

    /*
    |--------------------------------------------------------------------------
    | HSK
    |--------------------------------------------------------------------------
    */

    public function hsk(
        Request $request,
        string $level
    ): never {
        /*
        |--------------------------------------------------------------------------
        | Vérifie le niveau
        |--------------------------------------------------------------------------
        */

        $allowedLevels = ['1', '2', '3', '4'];

        if (!in_array($level, $allowedLevels, true)) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Prépare le niveau HSK
        |--------------------------------------------------------------------------
        */

        $hskLevel = 'HSK' . $level;

        /*
        |--------------------------------------------------------------------------
        | Récupère les règles de grammaire
        |--------------------------------------------------------------------------
        */

        $grammaires = $this->chinoisGrammaireRepository
            ->findByLevel($hskLevel);

        /*
        |--------------------------------------------------------------------------
        | Meta
        |--------------------------------------------------------------------------
        */

        $this->title =
            'Chinois | Grammaire '
            . $hskLevel;

        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        $this->render('chinois/hsk', [
            'grammaires' => $grammaires,
            'level' => $level,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Flashcards
    |--------------------------------------------------------------------------
    */

    public function flashcards(Request $request): never
    {
        $this->title = 'Chinois | Flashcards';

        $this->render('chinois/flashcards');
    }

    /*
    |--------------------------------------------------------------------------
    | Ajouter
    |--------------------------------------------------------------------------
    */

    public function ajouter(Request $request): never
    {
        $this->title = 'Chinois | Ajouter';

        $this->render('chinois/ajouter');
    }
}
