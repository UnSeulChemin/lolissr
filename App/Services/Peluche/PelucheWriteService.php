<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Peluche\Inputs\PelucheCreateDTO;
use App\DTO\Peluche\Inputs\PelucheUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Peluche;
use App\Repositories\Peluche\PelucheRepository;
use App\Services\UploadService;
use App\Services\User\UserLevelService;

use Framework\Cache\Cache;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use Throwable;

final readonly class PelucheWriteService
{
    public function __construct(
        private PelucheRepository $pelucheRepository,
        private UploadService $uploadService,
        private Database $database,
        private UserLevelService $userLevelService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | PELUCHE
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string,mixed> $files
     */
    public function create(PelucheCreateDTO $dto, array $files): ServiceResult
    {
        $existingPeluche = $this->pelucheRepository
            ->findOneBySlugAndNumero(
                $dto->slug,
                $dto->numero,
            );

        if ($existingPeluche !== null)
        {
            return $this->error(
                'Cette peluche existe déjà',
                409,
            );
        }

        return $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->origin,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('peluche'),
                    $files,
                );

                if (! $upload->success)
                {
                    return $this->error(
                        $upload->message,
                        $upload->status,
                    );
                }

                $uploadData = $upload->data['upload'] ?? null;

                if (! $uploadData instanceof UploadThumbnailData)
                {
                    return $this->error('Upload invalide');
                }

                try
                {
                    $inserted = $this->pelucheRepository->insert([
                        'thumbnail' => $uploadData->thumbnailPath,
                        'extension' => $uploadData->extension,
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
                        'Erreur lors de l’enregistrement',
                    );

                    if ($failure !== null)
                    {
                        $this->rollbackUpload($uploadData);

                        return $failure;
                    }

                    $this->clearCache();

                    return $this->success(
                        'Peluche ajoutée avec succès',
                    );
                }
                catch (Throwable $exception)
                {
                    $this->rollbackUpload($uploadData);

                    throw $exception;
                }
            },
        );
    }

    public function update(
        string $slug,
        int $numero,
        PelucheUpdateDTO $dto
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto
            ): ServiceResult
            {
                $updated = $this->pelucheRepository->updatePeluche(
                    $slug,
                    $numero,
                    $dto,
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update peluche',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour',
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->clearCache();

                return $this->success(
                    'Peluche mise à jour avec succès',
                );
            },
        );
    }

    public function updateCollectStatus(
        string $slug,
        int $numero,
        int $collectStatus
    ): ServiceResult
    {
        if (! in_array($collectStatus, [0, 1], true))
        {
            return $this->error(
                'Statut de collection invalide',
                422,
            );
        }

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $collectStatus
            ): ServiceResult
            {
                $peluche = $this->pelucheRepository
                    ->findOneBySlugAndNumero(
                        $slug,
                        $numero,
                    );

                if ($peluche === null)
                {
                    return $this->error(
                        'Peluche introuvable',
                        404,
                    );
                }

                $updated = $this->pelucheRepository->updateCollectStatus(
                    $slug,
                    $numero,
                    $collectStatus === 1,
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update collect status',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour',
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $xpEarned = false;

                if (
                    ! $peluche->collect
                    && $collectStatus === 1
                    && ! $peluche->collect_rewarded
                )
                {
                    [
                        'xpEarned' => $xpEarned,
                    ] = $this->rewardCollectXp(
                        $peluche,
                    );
                }

                $this->clearCache();

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Peluche marquée comme collectée'
                        : 'Peluche marquée comme non collectée',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'level' => $user?->level,
                        'xp' => $user?->xp,
                    ],
                );
            },
        );
    }

    public function delete(string $slug, int $numero): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero
            ): ServiceResult
            {
                $peluche = $this->pelucheRepository
                    ->findOneBySlugAndNumero(
                        $slug,
                        $numero,
                    );

                if ($peluche === null)
                {
                    return $this->error(
                        'Peluche introuvable',
                        404,
                    );
                }

                $deleted = $this->pelucheRepository
                    ->deleteBySlugAndNumero(
                        $slug,
                        $numero,
                    );

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete peluche',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression',
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->removeThumbnail($peluche);

                $this->clearCache();

                return $this->success(
                    'Peluche supprimée avec succès',
                );
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    private function rollbackUpload(UploadThumbnailData $upload): void
    {
        $this->uploadService->removeFile(
            $upload->destinationPath,
        );
    }

    private function removeThumbnail(Peluche $peluche): void
    {
        if (
            $peluche->thumbnail === ''
            || $peluche->extension === ''
        )
        {
            return;
        }

        $path =
            UploadConfig::thumbnailDirectory('peluche')
            . $peluche->thumbnail
            . '.'
            . $peluche->extension;

        $this->uploadService->removeFile($path);
    }

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     xpEarned: bool
     * }
     */
    private function rewardCollectXp(Peluche $peluche): array
    {
        $user = user();

        if ($user === null)
        {
            return [
                'xpEarned' => false,
            ];
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COLLECT_PELUCHE,
        );

        $this->pelucheRepository->markXpRewarded(
            $peluche->id,
        );

        return [
            'xpEarned' => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function writeFailed(
        bool $result,
        string $action,
        string $slug,
        int $numero,
        string $message
    ): ?ServiceResult
    {
        if ($result)
        {
            return null;
        }

        $this->logFailure(
            $action,
            $slug,
            $numero,
        );

        return $this->error($message);
    }

    private function clearCache(): void
    {
        Cache::forget('home.dashboard');
    }

    private function logFailure(
        string $action,
        string $slug,
        int $numero
    ): void
    {
        Logger::error(
            "{$action} échoué slug={$slug} numero={$numero}",
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private function success(
        string $message,
        array $data = [],
        int $status = 200
    ): ServiceResult
    {
        return ServiceResult::success(
            message: $message,
            data: $data,
            status: $status,
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    private function error(
        string $message,
        int $status = 500,
        array $data = []
    ): ServiceResult
    {
        return ServiceResult::error(
            message: $message,
            data: $data,
            status: $status,
        );
    }
}