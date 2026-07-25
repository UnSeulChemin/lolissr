<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Nendoroid\Inputs\NendoroidCreateDTO;
use App\DTO\Nendoroid\Inputs\NendoroidUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Services\UploadService;
use App\Services\User\UserLevelService;

use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use PDOException;
use Throwable;

final readonly class NendoroidWriteService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private UploadService $uploadService,
        private Database $database,
        private UserLevelService $userLevelService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | NENDOROID
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     */
    public function create(NendoroidCreateDTO $dto, array $files): ServiceResult
    {
        $existingNendoroid = $this->nendoroidRepository->findOneBySlugAndNumero($dto->slug, $dto->numero);

        if ($existingNendoroid !== null)
        {
            return $this->error('Ce Nendoroid existe déjà', 409);
        }

        return $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->origin,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('nendoroid'),
                    $files
                );

                if (! $upload->success)
                {
                    return $this->error($upload->message, $upload->status);
                }

                $uploadData = $upload->data['upload'] ?? null;

                if (! $uploadData instanceof UploadThumbnailData)
                {
                    return $this->error('Upload invalide');
                }

                try
                {
                    $inserted = $this->nendoroidRepository->insert([
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
                        'Insertion nendoroid',
                        $dto->slug,
                        $dto->numero,
                        'Erreur lors de l’enregistrement'
                    );

                    if ($failure !== null)
                    {
                        $this->rollbackUpload($uploadData);

                        return $failure;
                    }

                    return $this->success('Nendoroid ajouté avec succès');
                }
                catch (PDOException $exception)
                {
                    $this->rollbackUpload($uploadData);

                    if ($this->isDuplicateKeyException($exception))
                    {
                        return $this->error('Ce Nendoroid existe déjà', 409);
                    }

                    throw $exception;
                }
                catch (Throwable $exception)
                {
                    $this->rollbackUpload($uploadData);

                    throw $exception;
                }
            }
        );
    }

    public function update(string $slug, int $numero, NendoroidUpdateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
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
    }

    public function updateCollectStatus(string $slug, int $numero, int $collectStatus): ServiceResult
    {
        if (! in_array($collectStatus, [0, 1], true))
        {
            return $this->error('Statut de collection invalide', 422);
        }

        return $this->database->transaction(
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
                    $xpEarned = $this->rewardCollectXp($nendoroid);
                }

                $user = user();

                return $this->success(
                    $collectStatus === 1
                        ? 'Nendoroid marqué comme collecté'
                        : 'Nendoroid marqué comme non collecté',
                    [
                        'collectStatus' => $collectStatus,
                        'xpEarned' => $xpEarned,
                        'level' => $user?->level,
                        'xp' => $user?->xp,
                    ]
                );
            }
        );
    }

    public function delete(string $slug, int $numero): ServiceResult
    {
        return $this->database->transaction(
            function () use ($slug, $numero): ServiceResult
            {
                $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero($slug, $numero);

                if ($nendoroid === null)
                {
                    return $this->error('Nendoroid introuvable', 404);
                }

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

                $this->removeThumbnail($nendoroid);

                return $this->success('Nendoroid supprimé avec succès');
            }
        );
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

    private function removeThumbnail(Nendoroid $nendoroid): void
    {
        if ($nendoroid->thumbnail === '' || $nendoroid->extension === '')
        {
            return;
        }

        $path = UploadConfig::thumbnailDirectory('nendoroid') . $nendoroid->thumbnail . '.' . $nendoroid->extension;

        $this->uploadService->removeFile($path);
    }

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    private function rewardCollectXp(Nendoroid $nendoroid): bool
    {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        if (! $this->nendoroidRepository->claimCollectReward($nendoroid->id))
        {
            return false;
        }

        $this->userLevelService->addXp($user, UserXp::COLLECT_NENDOROID);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function isDuplicateKeyException(PDOException $exception): bool
    {
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

        $this->logFailure($action, $slug, $numero);

        return $this->error($message);
    }

    private function logFailure(string $action, string $slug, int $numero): void
    {
        Logger::error("{$action} échoué slug={$slug} numero={$numero}");
    }

    /**
     * @param array<string, mixed> $data
     */
    private function success(string $message, array $data = [], int $status = 200): ServiceResult
    {
        return ServiceResult::success(message: $message, data: $data, status: $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function error(string $message, int $status = 500, array $data = []): ServiceResult
    {
        return ServiceResult::error(message: $message, data: $data, status: $status);
    }
}