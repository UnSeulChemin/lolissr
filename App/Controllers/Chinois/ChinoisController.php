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
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository,
    ) {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Accueil
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): void
    {
        $this->title = 'Chinois';

        $this->render('chinois/index');
    }

    /*
    |--------------------------------------------------------------------------
    | Mandarin
    |--------------------------------------------------------------------------
    */

    public function mandarin(Request $request): void
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

    public function jinyu(Request $request): void
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

    public function grammaire(Request $request): void
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
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Vérifie le niveau
        |--------------------------------------------------------------------------
        */

        $allowedLevels = ['1', '2', '3', '4'];

        if (! in_array($level, $allowedLevels, true))
        {
            abort404();
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

        $this->render(
            'chinois/grammaire/hsk' . $level,
            ['grammaires' => $grammaires]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Flashcards
    |--------------------------------------------------------------------------
    */

    public function flashcards(Request $request): void
    {
        $this->title = 'Chinois | Flashcards';

        $this->render('chinois/flashcards');
    }

    /*
    |--------------------------------------------------------------------------
    | Ajouter
    |--------------------------------------------------------------------------
    */

    public function ajouter(Request $request): void
    {
        $this->title = 'Chinois | Ajouter';

        $this->render('chinois/ajouter');
    }
}