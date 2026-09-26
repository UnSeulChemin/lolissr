<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Nendoroid\Inputs\NendoroidCreateDTO;
use App\DTO\Nendoroid\Inputs\NendoroidUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Services\Media\ThumbnailManager;
use App\Services\Media\CollectionCreationService;

use Framework\Database\Database;
use Framework\Support\Logger;


final readonly class NendoroidWriteService
{
    use \App\Services\Collections\CollectionWriteResults;

    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private NendoroidXpRewardService $nendoroidXpRewardService,
        private CollectionCreationService $creationService,
        private DashboardCache $dashboardCache
    ) {
    }

    // =========================================
    // CREATE
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function create(NendoroidCreateDTO $dto, array $files): ServiceResult
    {
        if ($this->nendoroidRepository->findOneBySlugAndNumero($dto->slug, $dto->numero) !== null)
        {
            return $this->error('Ce Nendoroid existe déjà', 409);
        }

        $result = $this->creationService->create(
            'nendoroid',
            $dto->origin,
            $dto->numero,
            $files,
            function (UploadThumbnailData $upload) use ($dto): ServiceResult
            {
                $inserted = $this->nendoroidRepository->insert([
                    'thumbnail' => $upload->thumbnailPath,
                    'extension' => $upload->extension,
                    'slug' => $dto->slug,
                    'numero' => $dto->numero,
                    'origin' => $dto->origin,
                    'waifu' => $dto->waifu,
                    'company' => $dto->company,
                    'release_date' => $dto->release_date,
                    'commentaire' => $dto->commentaire,
                ]);

                $failure = $this->writeFailed(
                    $inserted,
                    'Insertion nendoroid',
                    $dto->slug,
                    $dto->numero,
                    'Erreur lors de l’enregistrement'
                );

                if ($failure !== null)
                {

                    return $failure;
                }

                return $this->success('Nendoroid ajouté avec succès');
            },
            'Ce Nendoroid existe déjà'
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // =========================================
    // UPDATE
    // =========================================

    public function update(string $slug, int $numero, NendoroidUpdateDTO $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->nendoroidRepository->updateNendoroid($slug, $numero, $dto);

                $failure = $this->writeFailed(
                    $updated,
                    'Update nendoroid',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Nendoroid mis à jour avec succès');
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // =========================================
    // UPDATE COLLECT STATUS
    // =========================================

    public function updateCollectStatus(string $slug, int $numero, int $collectStatus): ServiceResult
    {
        if (! in_array($collectStatus, [0, 1], true))
        {
            return $this->error('Statut de collection invalide', 422);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $collectStatus): ServiceResult
            {
                $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero($slug, $numero);

                if ($nendoroid === null)
                {
                    return $this->error('Nendoroid introuvable', 404);
                }

                $updated = $this->nendoroidRepository->updateCollectStatus(
                    $slug,
                    $numero,
                    $collectStatus === 1
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update collect status',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $xpEarned = false;

                if (! $nendoroid->collect && $collectStatus === 1)
                {
                    $xpEarned = $this->nendoroidXpRewardService->rewardCollect($nendoroid);
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Nendoroid marqué comme collecté'
                        : 'Nendoroid marqué comme non collecté',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? UserXp::COLLECT_NENDOROID : 0,
                        'level' => $user?->level,
                        'xp' => $user?->xp,
                    ]
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // =========================================
    // DELETE
    // =========================================

    public function delete(string $slug, int $numero): ServiceResult
    {
        $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero($slug, $numero);

        if ($nendoroid === null)
        {
            return $this->error('Nendoroid introuvable', 404);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->nendoroidRepository->deleteBySlugAndNumero($slug, $numero);

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete nendoroid',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Nendoroid supprimé avec succès');
            }
        );

        if (! $result->success)
        {
            return $result;
        }

        if (! $this->thumbnailManager->remove($nendoroid->thumbnail, $nendoroid->extension, 'nendoroid'))
        {
            Logger::warning(
                "Nendoroid supprimé mais thumbnail non supprimée slug={$slug} numero={$numero}"
            );
        }

        $this->forgetDashboardCache();

        return $result;
    }

    // =========================================
    // CACHE
    // =========================================

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
    }

}