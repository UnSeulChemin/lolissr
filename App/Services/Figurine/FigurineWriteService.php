<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\Cache\DashboardCache;
use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Figurine\Inputs\FigurineCreateDTO;
use App\DTO\Figurine\Inputs\FigurineUpdateDTO;
use App\Models\Figurine;
use App\Repositories\Figurine\FigurineRepository;
use App\Services\Media\ThumbnailManager;
use App\Services\User\UserLevelService;

use Framework\Database\Database;
use Framework\Support\Logger;

use PDOException;
use Throwable;

final readonly class FigurineWriteService
{
    public function __construct(
        private FigurineRepository $figurineRepository,
        private ThumbnailManager $thumbnailManager,
        private Database $database,
        private UserLevelService $userLevelService,
        private DashboardCache $dashboardCache
    ) {
    }

    // =========================================
    // FIGURINE
    // =========================================

    /**
     * @param array<string, mixed> $files
     */
    public function create(
        FigurineCreateDTO $dto,
        array $files
    ): ServiceResult {
        $existingFigurine = $this->figurineRepository->findOneBySlugAndNumero(
            $dto->slug,
            $dto->numero
        );

        if ($existingFigurine !== null)
        {
            return $this->error(
                'Cette figurine existe déjà',
                409
            );
        }

        $result = $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->thumbnailManager->upload(
                    'figurine',
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
                    $inserted = $this->figurineRepository->insert([
                        'thumbnail' => $upload->thumbnailPath,
                        'extension' => $upload->extension,
                        'slug' => $dto->slug,
                        'numero' => $dto->numero,

                        'origin' => $dto->origin,
                        'waifu' => $dto->waifu,
                        'scale' => $dto->scale,
                        'height_cm' => $dto->height_cm,
                        'company' => $dto->company,
                        'release_date' => $dto->release_date,

                        'commentaire' => $dto->commentaire,
                    ]);

                    $failure = $this->writeFailed(
                        $inserted,
                        'Insertion figurine',
                        $dto->slug,
                        $dto->numero,
                        'Erreur lors de l’enregistrement'
                    );

                    if ($failure !== null)
                    {
                        $this->thumbnailManager->rollback($upload);

                        return $failure;
                    }

                    return $this->success(
                        'Figurine ajoutée avec succès'
                    );
                }
                catch (PDOException $exception)
                {
                    $this->thumbnailManager->rollback($upload);

                    if ($this->isDuplicateKeyException($exception))
                    {
                        return $this->error(
                            'Cette figurine existe déjà',
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
            $this->dashboardCache->forget();
        }

        return $result;
    }

    public function update(
        string $slug,
        int $numero,
        FigurineUpdateDTO $dto
    ): ServiceResult {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $updated = $this->figurineRepository->updateFigurine(
                    $slug,
                    $numero,
                    $dto
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update figurine',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success(
                    'Figurine mise à jour avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->dashboardCache->forget();
        }

        return $result;
    }

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
                $figurine = $this->figurineRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero
                );

                if ($figurine === null)
                {
                    return $this->error(
                        'Figurine introuvable',
                        404
                    );
                }

                $updated = $this->figurineRepository->updateCollectStatus(
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

                if (! $figurine->collect && $collectStatus === 1)
                {
                    $xpEarned = $this->rewardCollectXp(
                        $figurine
                    );
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Figurine marquée comme collectée'
                        : 'Figurine marquée comme non collectée',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? UserXp::COLLECT_FIGURINE : 0,
                        'level' => $user?->level,
                        'xp' => $user?->xp,
                    ]
                );
            }
        );

        if ($result->success)
        {
            $this->dashboardCache->forget();
        }

        return $result;
    }

    public function delete(
        string $slug,
        int $numero
    ): ServiceResult {
        $figurine = $this->figurineRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($figurine === null)
        {
            return $this->error(
                'Figurine introuvable',
                404
            );
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $deleted = $this->figurineRepository->deleteBySlugAndNumero(
                    $slug,
                    $numero
                );

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete figurine',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success(
                    'Figurine supprimée avec succès'
                );
            }
        );

        if ($result->success)
        {
            $this->thumbnailManager->remove(
                $figurine->thumbnail,
                $figurine->extension,
                'figurine'
            );

            $this->dashboardCache->forget();
        }

        return $result;
    }

    // =========================================
    // XP
    // =========================================

    private function rewardCollectXp(
        Figurine $figurine
    ): bool {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        if (! $this->figurineRepository->claimCollectReward($figurine->id))
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COLLECT_FIGURINE
        );

        return true;
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

        Logger::error(
            "{$action} échoué slug={$slug} numero={$numero}"
        );

        return $this->error(
            $message
        );
    }

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