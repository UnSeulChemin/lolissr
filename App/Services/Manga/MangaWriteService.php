<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\DTO\Common\ServiceResultData;
use App\DTO\Manga\DeleteResultData;
use App\DTO\Manga\MangaCreateDTO;
use App\DTO\Manga\MangaUpdateDTO;
use App\DTO\Manga\MangaUpdateNoteDTO;
use App\DTO\Manga\UpdateLuResultData;
use App\DTO\Upload\UploadThumbnailData;
use App\Repositories\Manga\MangaRepository;
use App\Services\UploadService;

final class MangaWriteService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository,
        private readonly UploadService $uploadService,
        private readonly MangaCacheService $cacheService
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    private function success(
        string $message,
        array $data = [],
        int $status = 200
    ): ServiceResultData {
        return new ServiceResultData(
            true,
            $status,
            $message,
            $data
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function error(
        string $message,
        int $status = 500,
        array $data = []
    ): ServiceResultData {
        return new ServiceResultData(
            false,
            $status,
            $message,
            $data
        );
    }

    private function isReadOnlyMode(): bool
    {
        return App::isReadOnly();
    }

    private function blockedWriteResponse(): ServiceResultData
    {
        return $this->error(
            'Écriture en base désactivée en mode test',
            403
        );
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

    /**
     * @param array<string, mixed> $files
     */
    public function create(
        MangaCreateDTO $dto,
        array $files
    ): ServiceResultData {
        if (
            $this->isReadOnlyMode()
            && !$this->uploadService->isTestUploadMode()
        ) {
            return $this->blockedWriteResponse();
        }

        $existingManga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $dto->slug,
                $dto->numero
            );

        if (
            !$this->uploadService->isTestUploadMode()
            && $existingManga !== null
        ) {
            return $this->error(
                'Ce manga existe déjà',
                409
            );
        }

        $upload = $this->uploadService
            ->uploadThumbnail(
                $dto->livre,
                $dto->numero,
                $files,
                'image'
            );

        if (!$upload->success)
        {
            return $this->error(
                $upload->message,
                $upload->status
            );
        }

        $uploadData = $upload->data;

        if (!$uploadData instanceof UploadThumbnailData)
        {
            return $this->error(
                'Upload invalide'
            );
        }

        if ($this->uploadService->isTestUploadMode())
        {
            return $this->success(
                'Upload test OK',
                [
                    'file' => basename(
                        $uploadData->destination
                    ),
                ]
            );
        }

        $inserted = $this->mangaRepository
            ->insert([
                'thumbnail' => $uploadData->thumbnail,
                'extension' => $uploadData->extension,
                'slug' => $dto->slug,
                'livre' => $dto->livre,
                'editeur' => $dto->editeur,
                'numero' => $dto->numero,
                'lu' => 0,
                'statut' => $dto->statut,
                'jacquette' => null,
                'livre_note' => null,
                'commentaire' => $dto->commentaire,
            ]);

        if ($inserted === false)
        {
            $this->uploadService
                ->removeFileIfExists(
                    $uploadData->destination
                );

            $this->logFailure(
                'Insertion manga',
                $dto->slug,
                $dto->numero
            );

            return $this->error(
                'Erreur lors de l’enregistrement'
            );
        }

        $this->cacheService->clear();

        return $this->success(
            'Manga ajouté avec succès'
        );
    }

    /**
     * @param array<string, mixed> $files
     */
    public function update(
        string $slug,
        int $numero,
        MangaUpdateDTO $dto,
        array $files
    ): ServiceResultData {
        if ($this->isReadOnlyMode())
        {
            return $this->blockedWriteResponse();
        }

        $updated = $this->mangaRepository
            ->updateManga(
                $slug,
                $numero,
                $dto->editeur,
                $dto->statut,
                $dto->jacquette,
                $dto->livreNote,
                $dto->commentaire
            );

        if ($updated === false)
        {
            $this->logFailure(
                'Update manga',
                $slug,
                $numero
            );

            return $this->error(
                'Erreur lors de la mise à jour'
            );
        }

        $this->cacheService->clear();

        return $this->success(
            'Manga mis à jour avec succès'
        );
    }

    public function updateNote(
        string $slug,
        int $numero,
        MangaUpdateNoteDTO $dto
    ): ServiceResultData {
        if ($this->isReadOnlyMode())
        {
            return $this->blockedWriteResponse();
        }

        $updated = $this->mangaRepository
            ->updateNote(
                $slug,
                $numero,
                $dto->jacquette,
                $dto->livreNote
            );

        if ($updated === false)
        {
            $this->logFailure(
                'Update note',
                $slug,
                $numero
            );

            return $this->error(
                'Erreur update note'
            );
        }

        $this->cacheService->clear();

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $slug,
                $numero
            );

        if ($manga === null)
        {
            return $this->error(
                'Manga introuvable',
                404
            );
        }

        return $this->success(
            'Notes mises à jour',
            [
                'jacquette' => $dto->jacquette,
                'livre_note' => $dto->livreNote,
                'note' => $manga->note
                    ?? (
                        $dto->jacquette
                        + $dto->livreNote
                    ),
            ]
        );
    }

    public function updateLu(
        string $slug,
        int $numero,
        int $lu
    ): UpdateLuResultData {
        if ($this->isReadOnlyMode())
        {
            return new UpdateLuResultData(
                success: false,
                message: 'Écriture en base désactivée en mode test',
                status: 403,
                lu: $lu
            );
        }

        if (!in_array($lu, [0, 1], true))
        {
            return new UpdateLuResultData(
                success: false,
                message: 'Statut de lecture invalide',
                status: 422,
                lu: $lu
            );
        }

        $updated = $this->mangaRepository
            ->updateLu(
                $slug,
                $numero,
                $lu === 1
            );

        if ($updated === false)
        {
            $this->logFailure(
                'Update lu',
                $slug,
                $numero
            );

            return new UpdateLuResultData(
                success: false,
                message: 'Erreur lors de la mise à jour',
                status: 500,
                lu: $lu
            );
        }

        $this->cacheService->clear();

        return new UpdateLuResultData(
            success: true,
            message: $lu === 1
                ? 'Manga marqué comme lu'
                : 'Manga marqué comme non lu',
            status: 200,
            lu: $lu
        );
    }

    public function delete(
        string $slug,
        int $numero
    ): DeleteResultData {
        if ($this->isReadOnlyMode())
        {
            return new DeleteResultData(
                success: false,
                message: 'Écriture en base désactivée en mode test',
                status: 403
            );
        }

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $slug,
                $numero
            );

        if ($manga === null)
        {
            return new DeleteResultData(
                success: false,
                message: 'Manga introuvable',
                status: 404
            );
        }

        $deleted = $this->mangaRepository
            ->deleteBySlugAndNumero(
                $slug,
                $numero
            );

        if ($deleted === false)
        {
            $this->logFailure(
                'Delete manga',
                $slug,
                $numero
            );

            return new DeleteResultData(
                success: false,
                message: 'Erreur lors de la suppression',
                status: 500
            );
        }

        $imagePath =
            UploadConfig::mangaThumbnailDirectory()
            . $manga->thumbnail
            . '.'
            . $manga->extension;

        $this->uploadService
            ->removeFileIfExists($imagePath);

        $this->cacheService->clear();

        return new DeleteResultData(
            success: true,
            message: 'Manga supprimé avec succès',
            status: 200
        );
    }
}