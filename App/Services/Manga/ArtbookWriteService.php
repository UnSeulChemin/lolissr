<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\ArtbookCreateDTO;
use App\DTO\Manga\Inputs\ArtbookUpdateDTO;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Artbook;
use App\Repositories\Manga\ArtbookRepository;
use App\Services\UploadService;

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
                    $dto->artbook,
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

                $uploadData =
                    $upload->data['upload']
                    ?? null;

                if (! $uploadData instanceof UploadThumbnailData)
                {
                    return $this->error(
                        'Upload invalide'
                    );
                }

                try
                {
                    $inserted =
                        $this->artbookRepository->insert([
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
                        $this->rollbackUpload(
                            $uploadData,
                        );

                        return $failure;
                    }

                    $this->clearCache();

                    return $this->success(
                        'Artbook ajouté avec succès',
                    );
                }
                catch (Throwable $exception)
                {
                    $this->rollbackUpload(
                        $uploadData,
                    );

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
                $updated =
                    $this->artbookRepository
                        ->updateArtbook(
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

                return $this->success(
                    'Artbook mis à jour avec succès',
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
                $artbook =
                    $this->artbookRepository
                        ->findOneBySlugAndNumero(
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

                $deleted =
                    $this->artbookRepository
                        ->deleteBySlugAndNumero(
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

                $this->removeThumbnail(
                    $artbook,
                );

                $this->clearCache();

                return $this->success(
                    'Artbook supprimé avec succès',
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
        Artbook $artbook
    ): void
    {
        if (
            $artbook->thumbnail === ''
            || $artbook->extension === ''
        ) {
            return;
        }

        $path =
            UploadConfig::thumbnailDirectory('artbook')
            . $artbook->thumbnail
            . '.'
            . $artbook->extension;

        $this->uploadService->removeFile(
            $path
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function clearCache(): void
    {
        Cache::forget(
            'home.dashboard'
        );
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

        return $this->error(
            $message
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
