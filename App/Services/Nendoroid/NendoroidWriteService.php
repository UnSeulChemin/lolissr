<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\Cache\DashboardCache;
use App\DTO\Common\ServiceResult;
use App\DTO\Nendoroid\Inputs\NendoroidCreateDTO;
use App\DTO\Nendoroid\Inputs\NendoroidUpdateDTO;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Services\Media\ThumbnailManager;

use Framework\Database\Database;
use Framework\Support\Logger;

use PDOException;
use Throwable;

final readonly class NendoroidWriteService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private NendoroidXpRewardService $nendoroidXpRewardService,
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
        NendoroidCreateDTO $dto,
        array $files
    ): ServiceResult {
        $existingNendoroid = $this->nendoroidRepository->findOneBySlugAndNumero(
            $dto->slug,
            $dto->numero
        );

        if ($existingNendoroid !== null)
        {
            return $this->error(
                'Ce Nendoroid existe déjà',
                409
            );
        }

        $result = $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->thumbnailManager->upload(
                    'nendoroid',
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
                        $this->thumbnailManager->rollback(
                            $upload
                        );

                        return $failure;
                    }

                    return $this->success(
                        'Nendoroid ajouté avec succès'
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
                            'Ce Nendoroid existe déjà',
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
        NendoroidUpdateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->nendoroidRepository->updateNendoroid(
                    $slug,
                    $numero,
                    $dto
                );

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

                return $this->success(
                    'Nendoroid mis à jour avec succès'
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
                $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero
                );

                if ($nendoroid === null)
                {
                    return $this->error(
                        'Nendoroid introuvable',
                        404
                    );
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
                    $xpEarned =
                        $this->nendoroidXpRewardService->rewardCollect(
                            $nendoroid
                        );
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Nendoroid marqué comme collecté'
                        : 'Nendoroid marqué comme non collecté',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned
                            ? UserXp::COLLECT_NENDOROID
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
        $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($nendoroid === null)
        {
            return $this->error(
                'Nendoroid introuvable',
                404
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->nendoroidRepository->deleteBySlugAndNumero(
                    $slug,
                    $numero
                );

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

                return $this->success(
                    'Nendoroid supprimé avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->thumbnailManager->remove(
                $nendoroid->thumbnail,
                $nendoroid->extension,
                'nendoroid'
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