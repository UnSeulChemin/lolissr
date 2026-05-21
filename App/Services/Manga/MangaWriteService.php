<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateDTO;
use App\DTO\Manga\Inputs\MangaUpdateDTO;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Results\UpdateNoteResultData;
use App\DTO\Upload\UploadThumbnailData;
use App\Repositories\Manga\MangaRepository;
use App\Services\UploadService;
use Framework\Application\App;
use Framework\Config\UploadConfig;
use Framework\Support\Logger;

final class MangaWriteService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository,
        private readonly UploadService $uploadService,
        private readonly MangaCacheService $cacheService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    private function success(
        string $message,
        array $data = [],
        int $status = 200,
    ): ServiceResult {
        return ServiceResult::success(
            message: $message,
            data: $data,
            status: $status,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function error(
        string $message,
        int $status = 500,
        array $data = [],
    ): ServiceResult {
        return ServiceResult::error(
            message: $message,
            data: $data,
            status: $status,
        );
    }

    private function isReadOnlyMode(): bool
    {
        return App::isReadOnly();
    }

    private function blockedWriteResponse(): ServiceResult
    {
        return $this->error(
            'Écriture en base désactivée en mode test',
            403,
        );
    }

    private function clearCache(): void
    {
        $this->cacheService->clear();
    }

    private function logFailure(
        string $action,
        string $slug,
        int $numero,
    ): void {
        Logger::error(
            "{$action} échoué slug={$slug} numero={$numero}",
        );
    }

    /**
     * @param array<string, mixed> $files
     */
    public function create(
        MangaCreateDTO $dto,
        array $files,
    ): ServiceResult {
        if (
            $this->isReadOnlyMode()
            && !$this->uploadService->isTestUploadMode()
        ) {
            return $this->blockedWriteResponse();
        }

        $existingManga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $dto->slug,
                $dto->numero,
            );

        if (
            !$this->uploadService->isTestUploadMode()
            && $existingManga !== null
        ) {
            return $this->error(
                'Ce manga existe déjà',
                409,
            );
        }

        $upload = $this->uploadService
            ->uploadThumbnail(
                $dto->livre,
                $dto->numero,
                $files,
                'image',
            );

        if (!$upload->success) {
            return $this->error(
                $upload->message,
                $upload->status,
            );
        }

        $uploadData = $upload->data;

        if (!$uploadData instanceof UploadThumbnailData) {
            return $this->error(
                'Upload invalide',
            );
        }

        if ($this->uploadService->isTestUploadMode()) {
            return $this->success(
                'Upload test OK',
                [
                    'file' => basename(
                        $uploadData->destination,
                    ),
                ],
            );
        }

        $inserted = $this->mangaRepository
            ->insert([
                'thumbnail' => $uploadData->thumbnailPath,
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

        if ($inserted === false) {
            $this->uploadService
                ->removeFileIfExists(
                    $uploadData->destination,
                );

            $this->logFailure(
                'Insertion manga',
                $dto->slug,
                $dto->numero,
            );

            return $this->error(
                'Erreur lors de l’enregistrement',
            );
        }

        $this->clearCache();

        return $this->success(
            'Manga ajouté avec succès',
        );
    }

    /**
     * @param array<string, mixed> $files
     */
    public function update(
        string $slug,
        int $numero,
        MangaUpdateDTO $dto,
        array $files,
    ): ServiceResult {
        if ($this->isReadOnlyMode()) {
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
                $dto->commentaire,
            );

        if ($updated === false) {
            $this->logFailure(
                'Update manga',
                $slug,
                $numero,
            );

            return $this->error(
                'Erreur lors de la mise à jour',
            );
        }

        $this->clearCache();

        return $this->success(
            'Manga mis à jour avec succès',
        );
    }

    public function updateNote(
        string $slug,
        int $numero,
        MangaUpdateNoteDTO $dto,
    ): ServiceResult {
        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $updated = $this->mangaRepository
            ->updateNote(
                $slug,
                $numero,
                $dto->jacquette,
                $dto->livreNote,
            );

        if ($updated === false) {
            $this->logFailure(
                'Update note',
                $slug,
                $numero,
            );

            return $this->error(
                'Erreur lors de la mise à jour des notes',
            );
        }

        $this->clearCache();

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $slug,
                $numero,
            );

        if ($manga === null) {
            return $this->error(
                'Manga introuvable',
                404,
            );
        }

        return $this->success(
            'Notes mises à jour',
            [
                'notes' => new UpdateNoteResultData(
                    jacquette: $dto->jacquette ?? 0,
                    livreNote: $dto->livreNote ?? 0,
                    note: $manga->note
                        ?? (
                            ($dto->jacquette ?? 0)
                            + ($dto->livreNote ?? 0)
                        ),
                ),
            ],
        );
    }

    public function updateLu(
        string $slug,
        int $numero,
        int $lu,
    ): ServiceResult {
        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        if (!in_array($lu, [0, 1], true)) {
            return $this->error(
                'Statut de lecture invalide',
                422,
            );
        }

        $updated = $this->mangaRepository
            ->updateLu(
                $slug,
                $numero,
                $lu === 1,
            );

        if ($updated === false) {
            $this->logFailure(
                'Update lu',
                $slug,
                $numero,
            );

            return $this->error(
                'Erreur lors de la mise à jour',
            );
        }

        $this->clearCache();

        return $this->success(
            $lu === 1
                ? 'Manga marqué comme lu'
                : 'Manga marqué comme non lu',
            [
                'lu' => $lu,
            ],
        );
    }

    public function delete(
        string $slug,
        int $numero,
    ): ServiceResult {
        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $slug,
                $numero,
            );

        if ($manga === null) {
            return $this->error(
                'Manga introuvable',
                404,
            );
        }

        $deleted = $this->mangaRepository
            ->deleteBySlugAndNumero(
                $slug,
                $numero,
            );

        if ($deleted === false) {
            $this->logFailure(
                'Delete manga',
                $slug,
                $numero,
            );

            return $this->error(
                'Erreur lors de la suppression',
            );
        }

        $imagePath =
            UploadConfig::mangaThumbnailDirectory()
            . $manga->thumbnail
            . '.'
            . $manga->extension;

        $this->uploadService
            ->removeFileIfExists(
                $imagePath,
            );

        $this->clearCache();

        return $this->success(
            'Manga supprimé avec succès',
        );
    }
}