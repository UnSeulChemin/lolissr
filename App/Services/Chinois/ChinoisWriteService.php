<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateDTO;
use App\DTO\Chinois\Inputs\ChinoisVocabulaireCreateDTO;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

use Framework\Database\Database;

final readonly class ChinoisWriteService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private Database $database
    ) {
    }

    public function createGrammaire(ChinoisGrammaireCreateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use ($dto): ServiceResult
            {
                $inserted = $this->grammaireRepository->insert([
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
                    'maitrise' => false,
                ]);

                if (! $inserted)
                {
                    return $this->error('Erreur lors de l’ajout');
                }

                return $this->success('Grammaire ajoutée avec succès');
            }
        );
    }

    public function createVocabulaire(ChinoisVocabulaireCreateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use ($dto): ServiceResult
            {
                $inserted = $this->vocabulaireRepository->insert([
                    'langue' => $dto->langue,
                    'mot' => $dto->mot,
                    'pinyin' => $dto->pinyin,
                    'type' => $dto->type,
                    'traduction' => $dto->traduction,
                    'exemple' => $dto->exemple,
                ]);

                if (! $inserted)
                {
                    return $this->error('Erreur lors de l’ajout');
                }

                return $this->success('Vocabulaire ajouté avec succès');
            }
        );
    }

    public function deleteGrammaire(int $id): ServiceResult
    {
        return $this->database->transaction(
            function () use ($id): ServiceResult
            {
                $deleted = $this->grammaireRepository->deleteGrammaire($id);

                if (! $deleted)
                {
                    return $this->error('Erreur lors de la suppression');
                }

                return $this->success('Grammaire supprimée avec succès');
            }
        );
    }

    public function deleteVocabulaire(int $id): ServiceResult
    {
        return $this->database->transaction(
            function () use ($id): ServiceResult
            {
                $deleted = $this->vocabulaireRepository->deleteVocabulaire($id);

                if (! $deleted)
                {
                    return $this->error('Erreur lors de la suppression');
                }

                return $this->success('Vocabulaire supprimé avec succès');
            }
        );
    }

    public function updateGrammaire(int $id, ChinoisGrammaireCreateDTO $dto): bool
    {
        return $this->grammaireRepository->updateGrammaire(
            $id,
            [
                'niveau' => $dto->niveau,
                'titre' => $dto->titre,
                'structure' => $dto->structure,
                'abreviation' => $dto->abreviation,
                'phrase' => $dto->phrase,
                'pinyin' => $dto->pinyin,
                'traduction' => $dto->traduction,
                'explication' => $dto->explication,
                'section' => $dto->section,
                'categorie' => $dto->categorie,
            ]
        );
    }

    public function updateVocabulaire(int $id, ChinoisVocabulaireCreateDTO $dto): bool
    {
        return $this->vocabulaireRepository->updateVocabulaire(
            $id,
            [
                'langue' => $dto->langue,
                'mot' => $dto->mot,
                'pinyin' => $dto->pinyin,
                'type' => $dto->type,
                'traduction' => $dto->traduction,
                'exemple' => $dto->exemple,
            ]
        );
    }

    public function toggleGrammaireMaitrise(int $id): bool
    {
        return $this->grammaireRepository->toggleMaitrise($id);
    }

    public function toggleVocabulaireMaitrise(int $id): bool
    {
        return $this->vocabulaireRepository->toggleMaitrise($id);
    }

    public function markGrammaireXpRewarded(int $id): bool
    {
        return $this->grammaireRepository->markXpRewarded($id);
    }

    public function markVocabulaireXpRewarded(int $id): bool
    {
        return $this->vocabulaireRepository->markXpRewarded($id);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function success(string $message): ServiceResult
    {
        return ServiceResult::success($message);
    }

    private function error(string $message): ServiceResult
    {
        return ServiceResult::error($message);
    }
}
