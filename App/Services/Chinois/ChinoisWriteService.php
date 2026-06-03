<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateDTO;
use App\DTO\Chinois\Inputs\ChinoisVocabulaireCreateDTO;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use Framework\Application\App;
use Framework\Database\Database;

final readonly class ChinoisWriteService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private Database $database,
    ) {
    }

    private function success(
        string $message,
    ): ServiceResult {
        return ServiceResult::success(
            $message,
        );
    }

    private function error(
        string $message,
        int $status = 400,
    ): ServiceResult {
        return ServiceResult::error(
            $message,
            status: $status,
        );
    }

    private function isReadOnlyMode(): bool
    {
        return App::isReadOnly();
    }

    public function createGrammaire(
        ChinoisGrammaireCreateDTO $dto,
    ): ServiceResult {

        if ($this->isReadOnlyMode())
        {
            return $this->error(
                'Écriture en base désactivée',
                403,
            );
        }

        return $this->database->transaction(
            function () use ($dto): ServiceResult {

                $inserted =
                    $this->grammaireRepository
                        ->insert([
                            'niveau' => $dto->niveau,

                            'section' => $dto->section,
                            'section_position' => 0,

                            'categorie' => $dto->categorie,
                            'categorie_position' => 0,

                            'titre' => $dto->titre,
                            'structure' => $dto->structure,
                            'abreviation' => $dto->abreviation,

                            'phrase' => $dto->phrase,
                            'pinyin' => $dto->pinyin,
                            'traduction' => $dto->traduction,

                            'explication' => $dto->explication,

                            'position' => 0,
                            'maitrise' => 0,
                        ]);

                if (! $inserted)
                {
                    return $this->error(
                        'Erreur lors de l’ajout',
                    );
                }

                return $this->success(
                    'Grammaire ajoutée avec succès',
                );
            },
        );
    }

    public function createVocabulaire(
        ChinoisVocabulaireCreateDTO $dto,
    ): ServiceResult {

        if ($this->isReadOnlyMode())
        {
            return $this->error(
                'Écriture en base désactivée',
                403,
            );
        }

        return $this->database->transaction(
            function () use ($dto): ServiceResult {

                $inserted =
                    $this->vocabulaireRepository
                        ->insert([
                            'langue' => $dto->langue,
                            'mot' => $dto->mot,
                            'pinyin' => $dto->pinyin,
                            'type' => $dto->type,
                            'traduction' => $dto->traduction,
                            'exemple' => $dto->exemple,
                        ]);

                if (! $inserted)
                {
                    return $this->error(
                        'Erreur lors de l’ajout',
                    );
                }

                return $this->success(
                    'Vocabulaire ajouté avec succès',
                );
            },
        );
    }

    public function deleteGrammaire(
        int $id,
    ): ServiceResult {

        if ($this->isReadOnlyMode())
        {
            return $this->error(
                'Écriture en base désactivée',
                403,
            );
        }

        return $this->database->transaction(
            function () use ($id): ServiceResult {

                $deleted =
                    $this->grammaireRepository
                        ->deleteGrammaire(
                            $id,
                        );

                if (! $deleted)
                {
                    return $this->error(
                        'Erreur lors de la suppression',
                    );
                }

                return $this->success(
                    'Grammaire supprimée avec succès',
                );
            },
        );
    }
}