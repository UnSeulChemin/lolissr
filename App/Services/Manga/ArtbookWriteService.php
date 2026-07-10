<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\ArtbookCreateDTO;
use App\DTO\Manga\Inputs\ArtbookUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Artbook;
use App\Repositories\Manga\ArtbookRepository;
use App\Services\UploadService;
use App\Services\User\UserLevelService;

use Framework\Cache\Cache;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use Throwable;

final readonly class ArtbookWriteService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private UploadService $uploadService,
        private Database $database,
        private UserLevelService $userLevelService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | ARTBOOK
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string,mixed> $files
     */
    public function create(ArtbookCreateDTO $dto, array $files): ServiceResult
    {
        $existingArtbook = $this->artbookRepository->findOneBySlugAndNumero($dto->slug, $dto->numero);

        if ($existingArtbook !== null)
        {
            return $this->error('Cet artbook existe déjà', 409);
        }

        return $this->database->transaction(
            function () use (
                $dto,
                $files,
            ): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->slug,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('artbook'),
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
                    $inserted = $this->artbookRepository->insert([
                        'thumbnail' => $uploadData->thumbnailPath,
                        'extension' => $uploadData->extension,
                        'slug' => $dto->slug,
                        'numero' => $dto->numero,

                        'artbook' => $dto->artbook,
                        'auteur' => $dto->auteur,
                        'serie' => $dto->serie,
                        'company' => $dto->company,
                        'release_date' => $dto->release_date,

                        'commentaire' => $dto->commentaire,
                    ]);

                    $failure = $this->writeFailed(
                        $inserted,
                        'Insertion artbook',
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

                    return $this->success('Artbook ajouté avec succès');
                }
                catch (Throwable $exception)
                {
                    $this->rollbackUpload($uploadData);

                    throw $exception;
                }
            }
        );
    }

    public function update(
        string $slug,
        int $numero,
        ArtbookUpdateDTO $dto,
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto,
            ): ServiceResult
            {
                $updated = $this->artbookRepository->updateArtbook(
                    $slug,
                    $numero,
                    $dto,
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update artbook',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour',
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->clearCache();

                return $this->success('Artbook mis à jour avec succès');
            }
        );
    }

    public function updateReadStatus(
        string $slug,
        int $numero,
        int $readStatus,
    ): ServiceResult
    {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error(
                'Statut de lecture invalide',
                422,
            );
        }

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $readStatus,
            ): ServiceResult
            {
                $artbook = $this->artbookRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero,
                );

                if ($artbook === null)
                {
                    return $this->error(
                        'Artbook introuvable',
                        404,
                    );
                }

                $updated = $this->artbookRepository->updateReadStatus(
                    $slug,
                    $numero,
                    $readStatus === 1,
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update artbook read status',
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
                    ! $artbook->lu
                    && $readStatus === 1
                    && ! $artbook->xp_read_rewarded
                )
                {
                    $xpEarned = $this->rewardReadXp($artbook);
                }

                $this->clearCache();

                $user = user();

                return $this->success(
                    $readStatus === 1
                        ? 'Artbook marqué comme lu'
                        : 'Artbook marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned
                            ? UserXp::READ_ARTBOOK
                            : 0,
                        'level' => $user?->level,
                        'xp' => $user?->xp,
                    ]
                );
            }
        );
    }

    public function delete(
        string $slug,
        int $numero,
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
            ): ServiceResult
            {
                $artbook = $this->artbookRepository->findOneBySlugAndNumero(
                    $slug,
                    $numero,
                );

                if ($artbook === null)
                {
                    return $this->error(
                        'Artbook introuvable',
                        404,
                    );
                }

                $deleted = $this->artbookRepository->deleteBySlugAndNumero(
                    $slug,
                    $numero,
                );

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete artbook',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression',
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->removeThumbnail($artbook);

                $this->clearCache();

                return $this->success('Artbook supprimé avec succès');
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    private function rewardReadXp(Artbook $artbook): bool
    {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::READ_ARTBOOK,
        );

        $this->artbookRepository->markXpRewarded($artbook->id);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    private function rollbackUpload(UploadThumbnailData $upload): void
    {
        $this->uploadService->removeFile($upload->destinationPath);
    }

    private function removeThumbnail(Artbook $artbook): void
    {
        if ($artbook->thumbnail === '' || $artbook->extension === '')
        {
            return;
        }

        $path = UploadConfig::thumbnailDirectory('artbook')
            . $artbook->thumbnail
            . '.'
            . $artbook->extension;

        $this->uploadService->removeFile($path);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

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
        Logger::error("{$action} échoué slug={$slug} numero={$numero}");
    }

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

        $this->logFailure($action, $slug, $numero);

        return $this->error($message);
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
            status: $status
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
            status: $status
        );
    }
}