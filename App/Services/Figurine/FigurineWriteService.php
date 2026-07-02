<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\DTO\Common\ServiceResult;
use App\DTO\Figurine\Inputs\FigurineCreateDTO;
use App\DTO\Figurine\Inputs\FigurineUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Figurine;
use App\Repositories\Figurine\FigurineRepository;
use App\Services\UploadService;

use Framework\Cache\Cache;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use Throwable;

final readonly class FigurineWriteService
{
    public function __construct(
        private FigurineRepository $figurineRepository,
        private UploadService $uploadService,
        private Database $database
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | FIGURINE
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string,mixed> $files
     */
    public function create(FigurineCreateDTO $dto, array $files): ServiceResult
    {
        $existing = $this->figurineRepository
            ->findOneBySlugAndNumero(
                $dto->slug,
                $dto->numero,
            );

        if ($existing !== null)
        {
            return $this->error(
                'Cette figurine existe déjà',
                409,
            );
        }

        return $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->origin,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('figurine'),
                    $files,
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
                    $inserted = $this->figurineRepository->insert([
                        'thumbnail' => $uploadData->thumbnailPath,
                        'extension' => $uploadData->extension,
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
                        $this->rollbackUpload($uploadData);

                        return $failure;
                    }

                    $this->clearCache();

                    return $this->success('Figurine ajoutée avec succès');
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
        FigurineUpdateDTO $dto
    ): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto
            ): ServiceResult
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

                $this->clearCache();

                return $this->success(
                    'Figurine mise à jour avec succès'
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
                $figurine = $this->figurineRepository
                    ->findOneBySlugAndNumero(
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

                $deleted = $this->figurineRepository
                    ->deleteBySlugAndNumero(
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

                $this->removeThumbnail($figurine);

                $this->clearCache();

                return $this->success(
                    'Figurine supprimée avec succès'
                );
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

    private function removeThumbnail(
        Figurine $figurine
    ): void
    {
        if (
            $figurine->thumbnail === ''
            || $figurine->extension === ''
        ) {
            return;
        }

        $path =
            UploadConfig::thumbnailDirectory('figurine')
            . $figurine->thumbnail
            . '.'
            . $figurine->extension;

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
    private function success(string $message, array $data = [], int $status = 200): ServiceResult
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
    private function error(string $message, int $status = 500, array $data = []): ServiceResult
    {
        return ServiceResult::error(
            message: $message,
            data: $data,
            status: $status
        );
    }
}