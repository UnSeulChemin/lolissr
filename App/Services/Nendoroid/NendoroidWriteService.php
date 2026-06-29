<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\DTO\Common\ServiceResult;
use App\DTO\Nendoroid\Inputs\NendoroidCreateDTO;
use App\DTO\Nendoroid\Inputs\NendoroidUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Services\UploadService;

use Framework\Cache\Cache;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use Throwable;

final readonly class NendoroidWriteService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private UploadService $uploadService,
        private Database $database
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | NENDOROIDS
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string,mixed> $files
     */
    public function create(
        NendoroidCreateDTO $dto,
        array $files
    ): ServiceResult
    {
        $existing = $this->nendoroidRepository
            ->findOneBySlugAndNumero(
                $dto->slug,
                $dto->numero,
            );

        if ($existing !== null)
        {
            return $this->error(
                'Cette Nendoroid existe déjà',
                409,
            );
        }

        return $this->database->transaction(
            function () use (
                $dto,
                $files
            ): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->waifu,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('nendoroids'),
                    $files,
                );

                if (! $upload->success)
                {
                    return $this->error(
                        $upload->message,
                        $upload->status
                    );
                }

                $uploadData = $upload->data['upload'] ?? null;

                if (! $uploadData instanceof UploadThumbnailData)
                {
                    return $this->error(
                        'Upload invalide'
                    );
                }

                try
                {
                    $inserted = $this->nendoroidRepository->insert([
                        'thumbnail' => $uploadData->thumbnailPath,
                        'extension' => $uploadData->extension,
                        'slug' => $dto->slug,
                        'numero' => $dto->numero,
                        'waifu' => $dto->waifu,
                        'company' => $dto->company,
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

                    $this->clearCache();

                    return $this->success(
                        'Nendoroid ajoutée avec succès'
                    );
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
        NendoroidUpdateDTO $dto
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto
            ): ServiceResult
            {
                $updated = $this->nendoroidRepository
                    ->updateNendoroid(
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

                $this->clearCache();

                return $this->success(
                    'Nendoroid mise à jour avec succès'
                );
            }
        );
    }

    public function delete(
        string $slug,
        int $numero
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero
            ): ServiceResult
            {
                $nendoroid = $this->nendoroidRepository
                    ->findOneBySlugAndNumero(
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

                $deleted = $this->nendoroidRepository
                    ->deleteBySlugAndNumero(
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

                $this->removeThumbnail($nendoroid);

                $this->clearCache();

                return $this->success(
                    'Nendoroid supprimée avec succès'
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    private function rollbackUpload(
        UploadThumbnailData $upload
    ): void
    {
        $this->uploadService->removeFile(
            $upload->destinationPath
        );
    }

    private function removeThumbnail(
        Nendoroid $nendoroid
    ): void
    {
        if (
            $nendoroid->thumbnail === ''
            || $nendoroid->extension === ''
        ) {
            return;
        }

        $path =
            UploadConfig::thumbnailDirectory('nendoroids')
            . $nendoroid->thumbnail
            . '.'
            . $nendoroid->extension;

        $this->uploadService->removeFile($path);
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
            $numero
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
            "{$action} échoué slug={$slug} numero={$numero}"
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