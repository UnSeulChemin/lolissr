<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;
use Framework\Http\Request;

final class ChinoisController extends Controller
{
    // Correction :
    // constante métier centralisée.
    // Évite les valeurs hardcodées répétées.
    private const HSK_LEVELS = [
        '1',
        '2',
        '3',
        '4',
    ];

    public function __construct(
        private readonly ChinoisReadService $chinoisReadService,
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Chinois';

        $this->render(
            'chinois/index',
        );
    }

    public function mandarin(): never
    {
        // Correction :
        // suppression multiline inutile.
        $this->title = 'Chinois | Mandarin';

        $this->render(
            'chinois/mandarin',
            [
                'vocabulaires' => $this
                    ->chinoisReadService
                    ->mandarin(),
            ],
        );
    }

    public function jinyu(): never
    {
        $this->title = 'Chinois | 晋语';

        $this->render(
            'chinois/jinyu',
            [
                'vocabulaires' => $this
                    ->chinoisReadService
                    ->jinyu(),
            ],
        );
    }

    public function grammaire(): never
    {
        $this->title = 'Chinois | Grammaire';

        $this->render(
            'chinois/grammaire',
        );
    }

    public function hsk(
        string $level,
    ): never {
        // Correction :
        // utilisation de la constante centralisée.
        // Plus maintenable si tu ajoutes HSK5/6 plus tard.
        if (
            !in_array(
                $level,
                self::HSK_LEVELS,
                true,
            )
        ) {
            $this->notFound();
        }

        // Très bon code déjà présent :
        // flow ultra lisible et simple.
        $hskLevel = 'HSK' . $level;

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

    public function flashcards(): never
    {
        $this->title = 'Chinois | Flashcards';

        $this->render(
            'chinois/flashcards',
        );
    }

    public function ajouter(): never
    {
        $this->title = 'Chinois | Ajouter';

        $this->render(
            'chinois/ajouter',
        );
    }
}