<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Constants\UserXp;
use App\DTO\Chinois\Inputs\ChinoisGrammaireCreateDTO;
use App\DTO\Chinois\Inputs\ChinoisVocabulaireCreateDTO;
use App\DTO\Common\ServiceResult;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Services\User\UserLevelService;

use Framework\Database\Database;

final readonly class ChinoisWriteService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private UserLevelService $userLevelService,
        private Database $database
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | GRAMMAIRE
    |--------------------------------------------------------------------------
    */

    public function createGrammaire(ChinoisGrammaireCreateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use ($dto): ServiceResult
            {
                $inserted = $this->grammaireRepository->insert([
                    'niveau' => $dto->niveau,
                    'section' => $dto->section,
                    'section_position' => $this->grammaireRepository->getSectionPosition($dto->niveau, $dto->section),
                    'categorie' => $dto->categorie,
                    'categorie_position' => $this->grammaireRepository->getCategoriePosition($dto->niveau, $dto->section, $dto->categorie),
                    'titre' => $dto->titre,
                    'structure' => $dto->structure,
                    'abreviation' => $dto->abreviation,
                    'phrase' => $dto->phrase,
                    'pinyin' => $dto->pinyin,
                    'traduction' => $dto->traduction,
                    'explication' => $dto->explication,
                    'position' => $this->grammaireRepository->getNextPosition($dto->niveau, $dto->section, $dto->categorie),
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
                    $dto->categorie,
                );

                if (! $updated)
                {
                    return $this->error('Erreur lors de la mise à jour');
                }

                return $this->success('Grammaire mise à jour avec succès');
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

    /*
    |--------------------------------------------------------------------------
    | VOCABULAIRE
    |--------------------------------------------------------------------------
    */

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
                    $dto->exemple,
                );

                if (! $updated)
                {
                    return $this->error('Erreur lors de la mise à jour');
                }

                return $this->success('Vocabulaire mis à jour avec succès');
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

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     maitrise: bool,
     *     xpEarned: bool
     * }
     */
    public function toggleGrammaireMaitrise(int $id): array
    {
        $maitrise = $this->grammaireRepository->toggleMaitrise($id);

        $xpEarned = false;

        if (
            $maitrise
            && ! $this->grammaireRepository->isXpRewarded($id)
        ) {
            $this->rewardGrammarXp($id);

            $xpEarned = true;
        }

        return [
            'maitrise' => $maitrise,
            'xpEarned' => $xpEarned,
        ];
    }

    /**
     * @return array{
     *     maitrise: bool,
     *     xpEarned: bool
     * }
     */
    public function toggleVocabulaireMaitrise(int $id): array
    {
        $maitrise = $this->vocabulaireRepository->toggleMaitrise($id);

        $xpEarned = false;

        if (
            $maitrise
            && ! $this->vocabulaireRepository->isXpRewarded($id)
        ) {
            $this->rewardVocabularyXp($id);

            $xpEarned = true;
        }

        return [
            'maitrise' => $maitrise,
            'xpEarned' => $xpEarned,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function rewardGrammarXp(int $id): void
    {
        $user = user();

        if ($user === null)
        {
            return;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::LEARN_GRAMMAR,
        );

        $this->grammaireRepository->markXpRewarded($id);
    }

    private function rewardVocabularyXp(int $id): void
    {
        $user = user();

        if ($user === null)
        {
            return;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::LEARN_VOCABULARY,
        );

        $this->vocabulaireRepository->markXpRewarded($id);
    }

    private function success(string $message): ServiceResult
    {
        return ServiceResult::success($message);
    }

    private function error(string $message): ServiceResult
    {
        return ServiceResult::error($message);
    }
}
