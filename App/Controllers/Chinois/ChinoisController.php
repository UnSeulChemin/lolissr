<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;
use Framework\Http\Request;

final class ChinoisController extends Controller
{
    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Accueil
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Chinois';

        $this->render(
            'chinois/index',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mandarin
    |--------------------------------------------------------------------------
    */

    public function mandarin(): never
    {
        $this->title =
            'Chinois | Mandarin';

        $this->render(
            'chinois/mandarin',
            [
                'vocabulaires' => $this
                    ->chinoisReadService
                    ->mandarin(),
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 晋语
    |--------------------------------------------------------------------------
    */

    public function jinyu(): never
    {
        $this->title =
            'Chinois | 晋语';

        $this->render(
            'chinois/jinyu',
            [
                'vocabulaires' => $this
                    ->chinoisReadService
                    ->jinyu(),
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Grammaire
    |--------------------------------------------------------------------------
    */

    public function grammaire(): never
    {
        $this->title =
            'Chinois | Grammaire';

        $this->render(
            'chinois/grammaire',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HSK
    |--------------------------------------------------------------------------
    */

    public function hsk(
        string $level,
    ): never {
        $allowedLevels = [
            '1',
            '2',
            '3',
            '4',
        ];

        if (
            !in_array(
                $level,
                $allowedLevels,
                true,
            )
        ) {
            $this->notFound();
        }

        $hskLevel =
            'HSK' . $level;

        $grammaires = $this
            ->chinoisGrammaireRepository
            ->findByLevel($hskLevel);

        $this->title =
            'Chinois | Grammaire '
            . $hskLevel;

        $this->render(
            'chinois/hsk',
            [
                'grammaires' => $grammaires,
                'level' => $level,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Flashcards
    |--------------------------------------------------------------------------
    */

    public function flashcards(): never
    {
        $this->title =
            'Chinois | Flashcards';

        $this->render(
            'chinois/flashcards',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ajouter
    |--------------------------------------------------------------------------
    */

    public function ajouter(): never
    {
        $this->title =
            'Chinois | Ajouter';

        $this->render(
            'chinois/ajouter',
        );
    }
}