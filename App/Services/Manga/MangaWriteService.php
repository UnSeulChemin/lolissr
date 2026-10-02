<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateData;
use App\DTO\Manga\Inputs\MangaUpdateData;
use App\DTO\Manga\Inputs\MangaUpdateNoteData;

use App\DTO\Media\UploadThumbnailData;
use App\Repositories\Manga\MangaRepository;
use App\Services\Media\ThumbnailManager;
use App\Services\Collections\CollectionCreationService;

use Framework\Database\Database;
use Framework\Logging\Logger;



final readonly class MangaWriteService
{
    use \App\Services\Collections\Concerns\BuildsCollectionWriteResults;

    public function __construct(
        private MangaRepository $mangaRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private MangaXpRewardService $mangaXpRewardService,
        private CollectionCreationService $creationService,
        private DashboardCache $dashboardCache
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     */
    public function create(MangaCreateData $dto, array $files): ServiceResult
    {
        if ($this->mangaRepository->findRecordBySlugAndNumero($dto->slug, $dto->numero) !== null)
        {
            return $this->error('Ce manga existe déjà', 409);
        }

        $result = $this->creationService->create(
            'manga',
            $dto->livre,
            $dto->numero,
            $files,
            function (UploadThumbnailData $upload) use ($dto): ServiceResult
            {
                $this->mangaRepository->lockSeries($dto->slug);
                $failure = $this->createManga($dto, $upload);

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Manga ajouté avec succès');
            },
            'Ce manga existe déjà'
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(string $slug, int $numero, MangaUpdateData $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
                $updated = $this->mangaRepository->updateManga(
                    $slug,
                    $numero,
                    $dto->editeur,
                    $dto->statut,
                    $dto->jacquette,
                    $dto->livreNote,
                    $dto->commentaire
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update manga',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->mangaXpRewardService->rewardSeriesAchievements();

                return $this->success('Manga mis à jour avec succès');
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    public function updateNote(string $slug, int $numero, MangaUpdateNoteData $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {

                $notes = $this->mangaRepository->updateNote(
                    $slug,
                    $numero,
                    $dto->jacquette,
                    $dto->livreNote
                );

                $failure = $this->writeFailed(
                    $notes !== false,
                    'Update note',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour des notes'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success(
                    'Notes mises à jour',
                    [
                        'notes' => $notes,
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

    /*
    |--------------------------------------------------------------------------
    | UPDATE READ STATUS
    |--------------------------------------------------------------------------
    */

    public function updateReadStatus(string $slug, int $numero, int $readStatus): ServiceResult
    {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error('Statut de lecture invalide', 422);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $readStatus): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
                $manga = $this->mangaRepository->findRecordBySlugAndNumero($slug, $numero);

                if ($manga === null)
                {
                    return $this->error('Manga introuvable', 404);
                }

                $updated = $this->mangaRepository->updateReadStatus(
                    $slug,
                    $numero,
                    $readStatus === 1
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update read status',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $xpEarned = false;
                $seriesXpEarned = false;

                if (! $manga->lu && $readStatus === 1)
                {
                    [
                        'xpEarned' => $xpEarned,
                        'seriesXpEarned' => $seriesXpEarned,
                    ] = $this->mangaXpRewardService->rewardRead($manga, $slug);
                }

                $user = user();

                return $this->success(
                    $readStatus === 1
                        ? 'Manga marqué comme lu'
                        : 'Manga marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? UserXp::READ_TOME : 0,
                        'seriesXpEarned' => $seriesXpEarned,
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

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(string $slug, int $numero): ServiceResult
    {
        $manga = $this->mangaRepository->findRecordBySlugAndNumero($slug, $numero);

        if ($manga === null)
        {
            return $this->error('Manga introuvable', 404);
        }

        $result = $this->database->transaction(
            function () use ($slug, $manga): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
                $deleted = $this->mangaRepository->deleteById($manga->id);

                if (!$deleted)
                {
                    return $this->error('Élément introuvable ou déjà supprimé', 404);
                }

                $this->mangaXpRewardService->rewardSeriesAchievements();

                return $this->success('Manga supprimé avec succès');
            }
        );

        if (! $result->success)
        {
            return $result;
        }

        if (! $this->thumbnailManager->remove($manga->thumbnail, $manga->extension, 'manga'))
        {
            Logger::warning(
                "Manga supprimé mais thumbnail non supprimée slug={$slug} numero={$numero}"
            );
        }

        $this->forgetDashboardCache();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */


    private function createManga(
        MangaCreateData $dto,
        UploadThumbnailData $uploadData
    ): ?ServiceResult {
        $inserted = $this->mangaRepository->insert([
            'thumbnail' => $uploadData->thumbnailPath,
            'extension' => $uploadData->extension,
            'slug' => $dto->slug,
            'livre' => $dto->livre,
            'editeur' => $dto->editeur,
            'numero' => $dto->numero,
            'statut' => $dto->statut,
            'jacquette' => 1,
            'livre_note' => 1,
            'note' => 2,
            'commentaire' => $dto->commentaire,
        ]);

        $failure = $this->writeFailed(
            $inserted,
            'Insertion manga',
            $dto->slug,
            $dto->numero,
            'Erreur lors de l’enregistrement'
        );

        if ($failure !== null)
        {

            return $failure;
        }

        return null;
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
}
