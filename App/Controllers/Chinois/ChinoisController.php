<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;
use App\Http\Requests\Chinois\ChinoisGrammaireCreateRequest;
use App\Http\Requests\Chinois\ChinoisVocabulaireCreateRequest;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Chinois\ChinoisWriteService;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
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
        private readonly ChinoisWriteService $chinoisWriteService,
        private readonly ChinoisGrammaireRepository $chinoisGrammaireRepository,
        Request $request,
    ) {
        parent::__construct(
            $request,
        );
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
        string $level,
    ): never {

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
            "HSK{$level}";

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

    public function createGrammaire(): never
    {
        $this->title =
            'Chinois | Ajouter une grammaire';

        $this->render(
            'pages/chinois/grammaire/ajouter',
        );
    }

    public function createVocabulaire(): never
    {
        $this->title =
            'Chinois | Ajouter du vocabulaire';

        $this->render(
            'pages/chinois/vocabulaire/ajouter',
        );
    }

    public function storeGrammaire(
        ChinoisGrammaireCreateRequest $request,
    ): never {

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $result =
            $this->chinoisWriteService
                ->createGrammaire(
                    $request->dto(),
                );

        $this->jsonResult(
            $result,
        );
    }

    public function storeVocabulaire(
        ChinoisVocabulaireCreateRequest $request,
    ): never {

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $result =
            $this->chinoisWriteService
                ->createVocabulaire(
                    $request->dto(),
                );

        $this->jsonResult(
            $result,
        );
    }

    public function editGrammaire(
        int $id,
    ): never
    {
        $grammaire =
            $this->chinoisGrammaireRepository
                ->findById($id);

        if ($grammaire === null)
        {
            throw new NotFoundException(
                'Grammaire introuvable',
            );
        }

        $this->title =
            'Chinois | Modifier une grammaire';

        $this->render(
            'pages/chinois/grammaire/modifier',
            [
                'grammaire' => $grammaire,
            ],
        );
    }

    public function updateGrammaire(
        ChinoisGrammaireCreateRequest $request,
        int $id,
    ): never {

        $grammaire =
            $this->chinoisGrammaireRepository
                ->findById($id);

        if ($grammaire === null)
        {
            throw new NotFoundException(
                'Grammaire introuvable',
            );
        }

        if ($request->fails())
        {
            throw new ValidationException(
                $request->errors(),
            );
        }

        $dto =
            $request->dto();

        $this->chinoisGrammaireRepository
            ->updateGrammaire(
                $id,
                [
                    'niveau' =>
                        $dto->niveau,

                    'titre' =>
                        $dto->titre,

                    'structure' =>
                        $dto->structure,

                    'abreviation' =>
                        $dto->abreviation,

                    'phrase' =>
                        $dto->phrase,

                    'pinyin' =>
                        $dto->pinyin,

                    'traduction' =>
                        $dto->traduction,

                    'explication' =>
                        $dto->explication,

                    'section' =>
                        $dto->section,

                    'categorie' =>
                        $dto->categorie,
                ],
            );

        $level =
            substr(
                $dto->niveau,
                3,
            );

        $this->redirectWithSuccess(
            'chinois/grammaire/hsk'
            . $level,
            'Grammaire modifiée.',
        );
    }
}