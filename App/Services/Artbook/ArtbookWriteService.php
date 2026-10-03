<?php

declare(strict_types=1);

namespace App\Services\Artbook;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Artbook\Inputs\ArtbookCreateData;
use App\DTO\Artbook\Inputs\ArtbookUpdateData;
use App\DTO\Common\ServiceResult;
use App\DTO\Media\UploadThumbnailData;
use App\Repositories\Artbook\ArtbookRepository;
use App\Services\Collections\CollectionCreationService;
use App\Services\Media\ThumbnailManager;

use Framework\Database\Database;
use Framework\Logging\Logger;

final readonly class ArtbookWriteService
{
    use \App\Services\Collections\Concerns\BuildsCollectionWriteResults;

    public function __construct(
        private ArtbookRepository $artbookRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private ArtbookXpRewardService $artbookXpRewardService,
        private CollectionCreationService $creationService,
        private DashboardCache $dashboardCache
    )
    {
    }

    // --------------------------------------------------------------------------
    // CRÉATION
    // --------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $files
     */
    public function create(ArtbookCreateData $dto, array $files): ServiceResult
    {
        if ($this->artbookRepository->findOneBySlugAndNumero($dto->slug, $dto->numero) !== null)
        {
            return $this->error('Ce artbook existe déjà', 409);
        }

        $result = $this->creationService->create(
            'artbook',
            $dto->slug,
            $dto->numero,
            $files,
            function (UploadThumbnailData $upload) use ($dto): ServiceResult
            {
                $inserted = $this->artbookRepository->insert([
                    'thumbnail' => $upload->thumbnailPath,
                    'extension' => $upload->extension,
                    'slug' => $dto->slug,
                    'numero' => $dto->numero,
                    'artbook' => $dto->artbook,
                    'auteur' => $dto->auteur,
                    'serie' => $dto->serie,
                    'company' => $dto->company,
                    'release_date' => $dto->release_date,
                    'commentaire' => $dto->commentaire
                ]);

                $failure = $this->writeFailed(
                    $inserted,
                    'Insertion artbook',
                    $dto->slug,
                    $dto->numero,
                    'Erreur lors de l’enregistrement'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Artbook ajouté avec succès');
            },
            'Ce artbook existe déjà'
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // --------------------------------------------------------------------------
    // MISE À JOUR
    // --------------------------------------------------------------------------

    public function update(string $slug, int $numero, ArtbookUpdateData $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->artbookRepository->updateArtbook($slug, $numero, $dto);

                $failure = $this->writeFailed(
                    $updated,
                    'Update artbook',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Artbook mis à jour avec succès');
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // --------------------------------------------------------------------------
    // MISE À JOUR DU STATUT DE LECTURE
    // --------------------------------------------------------------------------

    public function updateReadStatus(string $slug, int $numero, int $readStatus): ServiceResult
    {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error('Statut de lecture invalide', 422);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $readStatus): ServiceResult
            {
                $artbook = $this->artbookRepository->updateReadStatus($slug, $numero, $readStatus === 1);

                $failure = $this->writeFailed(
                    $artbook !== false,
                    'Update artbook read status',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $xpEarned = false;

                assert($artbook instanceof \App\Models\Artbook);

                if (! $artbook->lu && $readStatus === 1)
                {
                    $xpEarned = $this->artbookXpRewardService->rewardArtbookRead($artbook);
                }

                $user = user();

                return $this->success(
                    $readStatus === 1
                        ? 'Artbook marqué comme lu'
                        : 'Artbook marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? UserXp::READ_ARTBOOK : 0,
                        'level' => $user?->level,
                        'xp' => $user?->xp
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

    // --------------------------------------------------------------------------
    // SUPPRESSION
    // --------------------------------------------------------------------------

    public function delete(string $slug, int $numero): ServiceResult
    {
        $artbook = $this->artbookRepository->findOneBySlugAndNumero($slug, $numero);

        if ($artbook === null)
        {
            return $this->error('Artbook introuvable', 404);
        }

        $result = $this->database->transaction(
            function () use ($artbook): ServiceResult
            {
                $deleted = $this->artbookRepository->deleteById($artbook->id);

                if (!$deleted)
                {
                    return $this->error('Élément introuvable ou déjà supprimé', 404);
                }

                return $this->success('Artbook supprimé avec succès');
            }
        );

        if (! $result->success)
        {
            return $result;
        }

        if (! $this->thumbnailManager->remove($artbook->thumbnail, $artbook->extension, 'artbook'))
        {
            Logger::warning(
                "Artbook supprimé mais thumbnail non supprimée slug={$slug} numero={$numero}"
            );
        }

        $this->forgetDashboardCache();

        return $result;
    }

    // --------------------------------------------------------------------------
    // CACHE
    // --------------------------------------------------------------------------

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
    }
}
