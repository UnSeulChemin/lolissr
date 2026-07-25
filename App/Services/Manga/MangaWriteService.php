<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateDTO;
use App\DTO\Manga\Inputs\MangaUpdateDTO;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Responses\MangaUpdateNoteData;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaStatsRepository;
use App\Services\Media\ThumbnailManager;
use App\Services\User\UserLevelService;

use Framework\Database\Database;
use Framework\Support\Logger;

use PDOException;
use RuntimeException;
use Throwable;

final readonly class MangaWriteService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaStatsRepository $mangaStatsRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private UserLevelService $userLevelService,
        private DashboardCache $dashboardCache
    ) {
    }

    // =========================================
    // MANGA
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function create(
        MangaCreateDTO $dto,
        array $files
    ): ServiceResult {
        $existingManga = $this->mangaRepository->findOneBySlugAndNumero(
            $dto->slug,
            $dto->numero
        );

        if ($existingManga !== null)
        {
            return $this->error(
                'Ce manga existe déjà',
                409
            );
        }

        $result = $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->thumbnailManager->upload(
                    'manga',
                    $dto->livre,
                    $dto->numero,
                    $files
                );

                if ($upload instanceof ServiceResult)
                {
                    return $upload;
                }

                try
                {
                    $failure = $this->createManga(
                        $dto,
                        $upload
                    );

                    if ($failure !== null)
                    {
                        return $failure;
                    }

                    return $this->success(
                        'Manga ajouté avec succès'
                    );
                }
                catch (PDOException $exception)
                {
                    $this->thumbnailManager->rollback($upload);

                    if ($this->isDuplicateKeyException($exception))
                    {
                        return $this->error(
                            'Ce manga existe déjà',
                            409
                        );
                    }

                    throw $exception;
                }
                catch (Throwable $exception)
                {
                    $this->thumbnailManager->rollback($upload);

                    throw $exception;
                }
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    public function update(
        string $slug,
        int $numero,
        MangaUpdateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
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

                return $this->success(
                    'Manga mis à jour avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    public function updateNote(
        string $slug,
        int $numero,
        MangaUpdateNoteDTO $dto
    ): ServiceResult {
        $existingManga = $this->mangaRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($existingManga === null)
        {
            return $this->error(
                'Manga introuvable',
                404
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->mangaRepository->updateNote(
                    $slug,
                    $numero,
                    $dto->jacquette,
                    $dto->livreNote
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update note',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour des notes'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $manga = $this->mangaRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero
                );

                if ($manga === null)
                {
                    throw new RuntimeException(
                        'Manga introuvable après la mise à jour'
                    );
                }

                return $this->success(
                    'Notes mises à jour',
                    [
                        'notes' => new MangaUpdateNoteData(
                            jacquette: $dto->jacquette ?? 0,
                            livreNote: $dto->livreNote ?? 0,
                            note: $manga->note ?? (($dto->jacquette ?? 0) + ($dto->livreNote ?? 0))
                        ),
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

    public function updateReadStatus(
        string $slug,
        int $numero,
        int $readStatus
    ): ServiceResult {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error(
                'Statut de lecture invalide',
                422
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $readStatus): ServiceResult
            {
                $manga = $this->mangaRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero
                );

                if ($manga === null)
                {
                    return $this->error(
                        'Manga introuvable',
                        404
                    );
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
                    ] = $this->rewardReadXp(
                        $manga,
                        $slug
                    );
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

    public function delete(
        string $slug,
        int $numero
    ): ServiceResult {
        $manga = $this->mangaRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($manga === null)
        {
            return $this->error(
                'Manga introuvable',
                404
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->mangaRepository->deleteBySlugAndNumero(
                    $slug,
                    $numero
                );

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete manga',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success(
                    'Manga supprimé avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->thumbnailManager->remove(
                $manga->thumbnail,
                $manga->extension,
                'manga'
            );

            $this->forgetDashboardCache();
        }

        return $result;
    }

    // =========================================
    // XP
    // =========================================

    /**
     * @return array{
     *     xpEarned: bool,
     *     seriesXpEarned: bool
     * }
     */
    private function rewardReadXp(
        Manga $manga,
        string $slug
    ): array {
        $user = user();

        if ($user === null)
        {
            return [
                'xpEarned' => false,
                'seriesXpEarned' => false,
            ];
        }

        if (! $this->mangaRepository->claimReadReward($manga->id))
        {
            return [
                'xpEarned' => false,
                'seriesXpEarned' => false,
            ];
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::READ_TOME
        );

        $seriesXpEarned = false;

        if (
            $this->mangaStatsRepository->isSeriesCompleted($slug)
            && $this->mangaRepository->claimSeriesReward($slug)
        )
        {
            $this->userLevelService->addXp(
                $user,
                UserXp::COMPLETE_SERIES
            );

            $seriesXpEarned = true;
        }

        return [
            'xpEarned' => true,
            'seriesXpEarned' => $seriesXpEarned,
        ];
    }

    // =========================================
    // HELPERS
    // =========================================

    private function isDuplicateKeyException(
        PDOException $exception
    ): bool {
        return $exception->getCode() === '23000'
            && ($exception->errorInfo[1] ?? null) === 1062;
    }

    private function logFailure(
        string $action,
        string $slug,
        int $numero
    ): void {
        Logger::error(
            "{$action} échoué slug={$slug} numero={$numero}"
        );
    }

    private function writeFailed(
        bool $result,
        string $action,
        string $slug,
        int $numero,
        string $message
    ): ?ServiceResult {
        if ($result)
        {
            return null;
        }

        $this->logFailure(
            $action,
            $slug,
            $numero
        );

        return $this->error($message);
    }

    private function createManga(
        MangaCreateDTO $dto,
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
            $this->thumbnailManager->rollback(
                $uploadData
            );

            return $failure;
        }

        return null;
    }

    // =========================================
    // CACHE
    // =========================================

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
    }

    // =========================================
    // RESULT
    // =========================================

    /**
     * @param array<string, mixed> $data
     */
    private function success(
        string $message,
        array $data = [],
        int $status = 200
    ): ServiceResult {
        return ServiceResult::success(
            message: $message,
            data: $data,
            status: $status
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function error(
        string $message,
        int $status = 500,
        array $data = []
    ): ServiceResult {
        return ServiceResult::error(
            message: $message,
            data: $data,
            status: $status
        );
    }
}