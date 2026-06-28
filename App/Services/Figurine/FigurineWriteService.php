<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\DTO\Common\ServiceResult;
use App\DTO\Figurine\Inputs\FigurineCreateDTO;
use App\DTO\Upload\UploadThumbnailData;
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
    | FIGURINES
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string,mixed> $files
     */
    public function create(FigurineCreateDTO $dto, array $files): ServiceResult
    {
        if ($this->figurineRepository->findBySlug($dto->slug) !== null)
        {
            return $this->error('Cette figurine existe déjà', 409);
        }

        return $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail($dto->waifu, 1, $files);

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
                        'waifu' => $dto->waifu,
                        'company' => $dto->company,
                        'commentaire' => $dto->commentaire,
                    ]);

                    if (! $inserted)
                    {
                        $this->rollbackUpload($uploadData);

                        $this->logFailure('Insertion figurine', $dto->slug);

                        return $this->error('Erreur lors de l’enregistrement');
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

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    private function rollbackUpload(UploadThumbnailData $upload): void
    {
        $this->uploadService->removeFile($upload->destinationPath);
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

    private function logFailure(string $action, string $slug): void
    {
        Logger::error("{$action} échoué slug={$slug}");
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