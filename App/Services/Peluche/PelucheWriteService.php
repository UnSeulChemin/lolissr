<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Peluche\Inputs\PelucheCreateDTO;
use App\DTO\Peluche\Inputs\PelucheUpdateDTO;
use App\Repositories\Peluche\PelucheRepository;
use App\Services\Media\ThumbnailManager;

use Framework\Database\Database;
use Framework\Support\Logger;

use PDOException;
use Throwable;

final readonly class PelucheWriteService
{
    public function __construct(
        private PelucheRepository $pelucheRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private PelucheXpRewardService $pelucheXpRewardService,
        private DashboardCache $dashboardCache
    ) {
    }


    // =========================================
    // CREATE
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function create(
        PelucheCreateDTO $dto,
        array $files
    ): ServiceResult {
        $existingPeluche = $this->pelucheRepository->findOneBySlugAndNumero(
            $dto->slug,
            $dto->numero
        );

        if ($existingPeluche !== null)
        {
            return $this->error(
                'Cette peluche existe déjà',
                409
            );
        }

        $result = $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->thumbnailManager->upload(
                    'peluche',
                    $dto->origin,
                    $dto->numero,
                    $files
                );

                if ($upload instanceof ServiceResult)
                {
                    return $upload;
                }

                try
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
                        $this->thumbnailManager->rollback(
                            $upload
                        );

                        return $failure;
                    }

                    return $this->success(
                        'Peluche ajoutée avec succès'
                    );
                }
                catch (PDOException $exception)
                {
                    $this->thumbnailManager->rollback(
                        $upload
                    );

                    if ($this->isDuplicateKeyException($exception))
                    {
                        return $this->error(
                            'Cette peluche existe déjà',
                            409
                        );
                    }

                    throw $exception;
                }
                catch (Throwable $exception)
                {
                    $this->thumbnailManager->rollback(
                        $upload
                    );

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


    // =========================================
    // UPDATE
    // =========================================

    public function update(
        string $slug,
        int $numero,
        PelucheUpdateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->pelucheRepository->updatePeluche(
                    $slug,
                    $numero,
                    $dto
                );

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

                return $this->success(
                    'Peluche mise à jour avec succès'
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
    // UPDATE COLLECT STATUS
    // =========================================

    public function updateCollectStatus(
        string $slug,
        int $numero,
        int $collectStatus
    ): ServiceResult {
        if (! in_array($collectStatus, [0, 1], true))
        {
            return $this->error(
                'Statut de collection invalide',
                422
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $collectStatus): ServiceResult
            {
                $peluche = $this->pelucheRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero
                );

                if ($peluche === null)
                {
                    return $this->error(
                        'Peluche introuvable',
                        404
                    );
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
                    $xpEarned =
                        $this->pelucheXpRewardService->rewardCollect(
                            $peluche
                        );
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Peluche marquée comme collectée'
                        : 'Peluche marquée comme non collectée',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned
                            ? UserXp::COLLECT_PELUCHE
                            : 0,
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

    public function delete(
        string $slug,
        int $numero
    ): ServiceResult {
        $peluche = $this->pelucheRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($peluche === null)
        {
            return $this->error(
                'Peluche introuvable',
                404
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->pelucheRepository->deleteBySlugAndNumero(
                    $slug,
                    $numero
                );

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

                return $this->success(
                    'Peluche supprimée avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->thumbnailManager->remove(
                $peluche->thumbnail,
                $peluche->extension,
                'peluche'
            );

            $this->forgetDashboardCache();
        }

        return $result;
    }


    // =========================================
    // CACHE
    // =========================================

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
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

        return $this->error(
            $message
        );
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