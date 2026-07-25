<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Cache\DashboardCache;
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
        private ChinoisXpRewardService $chinoisXpRewardService,
        private Database $database,
        private DashboardCache $dashboardCache
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | GRAMMAIRE
    |--------------------------------------------------------------------------
    */

    public function createGrammaire(
        ChinoisGrammaireCreateDTO $dto
    ): ServiceResult {
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
                    'maitrise' => false,
                ]);

                if (! $inserted)
                {
                    return $this->error(
                        'Erreur lors de l’ajout'
                    );
                }

                return $this->success(
                    'Grammaire ajoutée avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    public function updateGrammaire(
        int $id,
        ChinoisGrammaireCreateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
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

                if (! $updated)
                {
                    return $this->error(
                        'Erreur lors de la mise à jour'
                    );
                }

                return $this->success(
                    'Grammaire mise à jour avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    public function deleteGrammaire(
        int $id
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($id): ServiceResult
            {
                if (! $this->grammaireRepository->deleteGrammaire($id))
                {
                    return $this->error(
                        'Erreur lors de la suppression'
                    );
                }

                return $this->success(
                    'Grammaire supprimée avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | VOCABULAIRE
    |--------------------------------------------------------------------------
    */

    public function createVocabulaire(
        ChinoisVocabulaireCreateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
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
                    return $this->error(
                        'Erreur lors de l’ajout'
                    );
                }

                return $this->success(
                    'Vocabulaire ajouté avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    public function updateVocabulaire(
        int $id,
        ChinoisVocabulaireCreateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
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

                if (! $updated)
                {
                    return $this->error(
                        'Erreur lors de la mise à jour'
                    );
                }

                return $this->success(
                    'Vocabulaire mis à jour avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    public function deleteVocabulaire(
        int $id
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($id): ServiceResult
            {
                if (! $this->vocabulaireRepository->deleteVocabulaire($id))
                {
                    return $this->error(
                        'Erreur lors de la suppression'
                    );
                }

                return $this->success(
                    'Vocabulaire supprimé avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | MAÎTRISE
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     maitrise: bool,
     *     xpEarned: bool
     * }
     */
    public function toggleGrammaireMaitrise(
        int $id
    ): array {
        $result = $this->database->transaction(
            function () use ($id): array
            {
                $maitrise = $this->grammaireRepository->toggleMaitrise($id);

                if ($maitrise === null)
                {
                    return [
                        'maitrise' => false,
                        'xpEarned' => false,
                    ];
                }

                return [
                    'maitrise' => $maitrise,
                    'xpEarned' => $maitrise
                        ? $this->chinoisXpRewardService->rewardGrammar($id)
                        : false,
                ];
            }
        );

        if ($result['maitrise'])
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }


    /**
     * @return array{
     *     maitrise: bool,
     *     xpEarned: bool
     * }
     */
    public function toggleVocabulaireMaitrise(
        int $id
    ): array {
        $result = $this->database->transaction(
            function () use ($id): array
            {
                $maitrise = $this->vocabulaireRepository->toggleMaitrise($id);

                if ($maitrise === null)
                {
                    return [
                        'maitrise' => false,
                        'xpEarned' => false,
                    ];
                }

                return [
                    'maitrise' => $maitrise,
                    'xpEarned' => $maitrise
                        ? $this->chinoisXpRewardService->rewardVocabulary($id)
                        : false,
                ];
            }
        );

        if ($result['maitrise'])
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
    }


    /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */

    private function success(
        string $message
    ): ServiceResult {
        return ServiceResult::success(
            message: $message
        );
    }


    private function error(
        string $message
    ): ServiceResult {
        return ServiceResult::error(
            message: $message
        );
    }
}