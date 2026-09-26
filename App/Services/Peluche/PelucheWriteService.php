<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Peluche\Inputs\PelucheCreateDTO;
use App\DTO\Peluche\Inputs\PelucheUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Repositories\Peluche\PelucheRepository;
use App\Services\Media\ThumbnailManager;
use App\Services\Media\CollectionCreationService;

use Framework\Database\Database;
use Framework\Support\Logger;


final readonly class PelucheWriteService
{
    use \App\Services\Collections\CollectionWriteResults;

    public function __construct(
        private PelucheRepository $pelucheRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private PelucheXpRewardService $pelucheXpRewardService,
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
    public function create(PelucheCreateDTO $dto, array $files): ServiceResult
    {
        if ($this->pelucheRepository->findOneBySlugAndNumero($dto->slug, $dto->numero) !== null)
        {
            return $this->error('Cette peluche existe déjà', 409);
        }

        $result = $this->creationService->create(
            'peluche',
            $dto->origin,
            $dto->numero,
            $files,
            function (UploadThumbnailData $upload) use ($dto): ServiceResult
            {
                $inserted = $this->pelucheRepository->insert([
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
                    'Insertion peluche',
                    $dto->slug,
                    $dto->numero,
                    'Erreur lors de l’enregistrement'
                );

                if ($failure !== null)
                {

                    return $failure;
                }

                return $this->success('Peluche ajoutée avec succès');
            },
            'Cette peluche existe déjà'
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

    public function update(string $slug, int $numero, PelucheUpdateDTO $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->pelucheRepository->updatePeluche($slug, $numero, $dto);

                $failure = $this->writeFailed(
                    $updated,
                    'Update peluche',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Peluche mise à jour avec succès');
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
                $peluche = $this->pelucheRepository->findOneBySlugAndNumero($slug, $numero);

                if ($peluche === null)
                {
                    return $this->error('Peluche introuvable', 404);
                }

                $updated = $this->pelucheRepository->updateCollectStatus(
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

                if (! $peluche->collect && $collectStatus === 1)
                {
                    $xpEarned = $this->pelucheXpRewardService->rewardCollect($peluche);
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Peluche marquée comme collectée'
                        : 'Peluche marquée comme non collectée',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? UserXp::COLLECT_PELUCHE : 0,
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
        $peluche = $this->pelucheRepository->findOneBySlugAndNumero($slug, $numero);

        if ($peluche === null)
        {
            return $this->error('Peluche introuvable', 404);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->pelucheRepository->deleteBySlugAndNumero($slug, $numero);

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete peluche',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Peluche supprimée avec succès');
            }
        );

        if (! $result->success)
        {
            return $result;
        }

        if (! $this->thumbnailManager->remove($peluche->thumbnail, $peluche->extension, 'peluche'))
        {
            Logger::warning(
                "Peluche supprimée mais thumbnail non supprimée slug={$slug} numero={$numero}"
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