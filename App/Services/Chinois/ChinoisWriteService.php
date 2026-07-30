<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Cache\DashboardCache;
use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateDTO;
use App\DTO\Chinois\Inputs\ChinoisVocabulaireCreateDTO;
use App\DTO\Chinois\Responses\ChinoisMaitriseData;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

use Framework\Database\Database;
use Framework\Exceptions\NotFoundException;

final readonly class ChinoisWriteService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisXpRewardService $chinoisXpRewardService,
        private Database $database,
        private DashboardCache $dashboardCache
    ) {
    }

    // =========================================
    // GRAMMAIRE
    // =========================================

    public function createGrammaire(ChinoisGrammaireCreateDTO $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($dto): ServiceResult
            {
                $inserted = $this->grammaireRepository->insert([
                    'niveau' => $dto->niveau,
                    'section' => $dto->section,
                    'section_position' => $this->grammaireRepository->getSectionPosition(
                        $dto->niveau,
                        $dto->section
                    ),
                    'categorie' => $dto->categorie,
                    'categorie_position' => $this->grammaireRepository->getCategoriePosition(
                        $dto->niveau,
                        $dto->section,
                        $dto->categorie
                    ),
                    'titre' => $dto->titre,
                    'structure' => $dto->structure,
                    'abreviation' => $dto->abreviation,
                    'phrase' => $dto->phrase,
                    'pinyin' => $dto->pinyin,
                    'traduction' => $dto->traduction,
                    'explication' => $dto->explication,
                    'position' => $this->grammaireRepository->getNextPosition(
                        $dto->niveau,
                        $dto->section,
                        $dto->categorie
                    ),
                    'maitrise' => false
                ]);

                return $inserted
                    ? $this->success('Grammaire ajoutée avec succès')
                    : $this->error('Erreur lors de l’ajout');
            }
        );

        $this->forgetDashboardCacheOnSuccess($result);

        return $result;
    }

    public function updateGrammaire(int $id, ChinoisGrammaireCreateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use ($id, $dto): ServiceResult
            {
                $updated = $this->grammaireRepository->updateGrammaire(
                    $id,
                    $dto->niveau,
                    $dto->titre,
                    $dto->structure,
                    $dto->abreviation,
                    $dto->phrase,
                    $dto->pinyin,
                    $dto->traduction,
                    $dto->explication,
                    $dto->section,
                    $dto->categorie
                );

                return $updated
                    ? $this->success('Grammaire mise à jour avec succès')
                    : $this->error('Erreur lors de la mise à jour');
            }
        );
    }

    public function deleteGrammaire(int $id): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($id): ServiceResult
            {
                return $this->grammaireRepository->deleteGrammaire($id)
                    ? $this->success('Grammaire supprimée avec succès')
                    : $this->error('Erreur lors de la suppression');
            }
        );

        $this->forgetDashboardCacheOnSuccess($result);

        return $result;
    }

    // =========================================
    // VOCABULAIRE
    // =========================================

    public function createVocabulaire(ChinoisVocabulaireCreateDTO $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($dto): ServiceResult
            {
                $inserted = $this->vocabulaireRepository->insert([
                    'langue' => $dto->langue,
                    'mot' => $dto->mot,
                    'pinyin' => $dto->pinyin,
                    'type' => $dto->type,
                    'traduction' => $dto->traduction,
                    'exemple' => $dto->exemple
                ]);

                return $inserted
                    ? $this->success('Vocabulaire ajouté avec succès')
                    : $this->error('Erreur lors de l’ajout');
            }
        );

        $this->forgetDashboardCacheOnSuccess($result);

        return $result;
    }

    public function updateVocabulaire(int $id, ChinoisVocabulaireCreateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use ($id, $dto): ServiceResult
            {
                $updated = $this->vocabulaireRepository->updateVocabulaire(
                    $id,
                    $dto->langue,
                    $dto->mot,
                    $dto->pinyin,
                    $dto->type,
                    $dto->traduction,
                    $dto->exemple
                );

                return $updated
                    ? $this->success('Vocabulaire mis à jour avec succès')
                    : $this->error('Erreur lors de la mise à jour');
            }
        );
    }

    public function deleteVocabulaire(int $id): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($id): ServiceResult
            {
                return $this->vocabulaireRepository->deleteVocabulaire($id)
                    ? $this->success('Vocabulaire supprimé avec succès')
                    : $this->error('Erreur lors de la suppression');
            }
        );

        $this->forgetDashboardCacheOnSuccess($result);

        return $result;
    }

    // =========================================
    // MAÎTRISE
    // =========================================

    public function toggleGrammaireMaitrise(int $id): ChinoisMaitriseData
    {
        $result = $this->database->transaction(
            function () use ($id): ChinoisMaitriseData
            {
                $maitrise = $this->grammaireRepository->toggleMaitrise($id);

                if ($maitrise === null)
                {
                    throw new NotFoundException('Grammaire introuvable');
                }

                return new ChinoisMaitriseData(
                    maitrise: $maitrise,
                    xpEarned: $maitrise
                        ? $this->chinoisXpRewardService->rewardGrammar($id)
                        : false
                );
            }
        );

        $this->dashboardCache->forget();

        return $result;
    }

    public function toggleVocabulaireMaitrise(int $id): ChinoisMaitriseData
    {
        $result = $this->database->transaction(
            function () use ($id): ChinoisMaitriseData
            {
                $maitrise = $this->vocabulaireRepository->toggleMaitrise($id);

                if ($maitrise === null)
                {
                    throw new NotFoundException('Vocabulaire introuvable');
                }

                return new ChinoisMaitriseData(
                    maitrise: $maitrise,
                    xpEarned: $maitrise
                        ? $this->chinoisXpRewardService->rewardVocabulary($id)
                        : false
                );
            }
        );

        $this->dashboardCache->forget();

        return $result;
    }

    // =========================================
    // CACHE
    // =========================================

    private function forgetDashboardCacheOnSuccess(ServiceResult $result): void
    {
        if ($result->success)
        {
            $this->dashboardCache->forget();
        }
    }

    // =========================================
    // RÉSULTATS
    // =========================================

    private function success(string $message): ServiceResult
    {
        return ServiceResult::success(message: $message);
    }

    private function error(string $message): ServiceResult
    {
        return ServiceResult::error(message: $message);
    }
}