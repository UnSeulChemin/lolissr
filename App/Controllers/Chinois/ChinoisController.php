<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class ChinoisController extends Controller
{
    /**
     * @var list<string>
     */
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
        $this->title =
            'Chinois';

        $this->render(
            'pages/chinois/index',
        );
    }

    public function mandarin(): never
    {
        $this->title =
            'Chinois | Mandarin';

        $this->render(
            'pages/chinois/mandarin',
            [
                'vocabulaires' =>
                    $this->chinoisReadService
                        ->mandarin(),
            ],
        );
    }

    public function jinyu(): never
    {
        $this->title =
            'Chinois | 晋语';

        $this->render(
            'pages/chinois/jinyu',
            [
                'vocabulaires' =>
                    $this->chinoisReadService
                        ->jinyu(),
            ],
        );
    }

    public function grammaire(): never
    {
        $this->title =
            'Chinois | Grammaire';

        $this->render(
            'pages/chinois/grammaire',
        );
    }

    public function hsk(
        int|string $level,
    ): never {

        $level =
            (string) $level;

        if (
            ! in_array(
                $level,
                self::HSK_LEVELS,
                true,
            )
        ) {
            throw new NotFoundException(
                'Niveau HSK introuvable',
            );
        }

        $hskLevel =
            'HSK' . $level;

        $grammaires =
            $this->chinoisGrammaireRepository
                ->findByLevel(
                    $hskLevel,
                );

        $this->title =
            'Chinois | Grammaire '
            . $hskLevel;

        $this->render(
            'pages/chinois/hsk',
            [
                'grammaires' =>
                    $grammaires,

                'level' =>
                    $level,
            ],
        );
    }

    public function flashcards(): never
    {
        $this->title =
            'Chinois | Flashcards';

        $this->render(
            'pages/chinois/flashcards',
        );
    }

    public function ajouter(): never
    {
        $this->title =
            'Chinois | Ajouter';

        $this->render(
            'pages/chinois/ajouter',
        );
    }
}