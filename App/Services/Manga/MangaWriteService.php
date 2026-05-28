<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateDTO;
use App\DTO\Manga\Inputs\MangaUpdateDTO;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Responses\UpdateNoteData;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Services\UploadService;
use Framework\Application\App;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;
use Throwable;

final readonly class MangaWriteService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private UploadService $uploadService,
        private MangaCacheService $cacheService,
        private Database $database,
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
            'Écriture en base désactivée en mode lecture seule',
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

    private function writeFailed(
        bool $result,
        string $action,
        string $slug,
        int $numero,
        string $message,
    ): ?ServiceResult {

        if ($result) {
            return null;
        }

        $this->logFailure(
            $action,
            $slug,
            $numero,
        );

        return $this->error($message);
    }

    private function removeThumbnail(
        Manga $manga,
    ): void {

        if (
            $manga->thumbnail === ''
            || $manga->extension === ''
        ) {
            return;
        }

        $path =
            UploadConfig::mangaThumbnailDirectory()
            . $manga->thumbnail
            . '.'
            . $manga->extension;

        $this->uploadService
            ->removeFile($path);
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
            && ! $this->uploadService->isTestUploadMode()
        ) {
            return $this->blockedWriteResponse();
        }

        $existingManga =
            $this->mangaRepository
                ->findOneBySlugAndNumero(
                    $dto->slug,
                    $dto->numero,
                );

        if (
            ! $this->uploadService->isTestUploadMode()
            && $existingManga !== null
        ) {
            return $this->error(
                'Ce manga existe déjà',
                409,
            );
        }

        return $this->database->transaction(
            function () use (
                $dto,
                $files,
            ): ServiceResult {

                $upload =
                    $this->uploadService
                        ->uploadThumbnail(
                            $dto->livre,
                            $dto->numero,
                            $files,
                        );

                if (! $upload->success) {
                    return $this->error(
                        $upload->message,
                        $upload->status,
                    );
                }

                $uploadData =
                    $upload->data['upload']
                    ?? null;

                if (
                    ! $uploadData instanceof UploadThumbnailData
                ) {
                    return $this->error(
                        'Upload invalide',
                    );
                }

                if (
                    $this->uploadService
                        ->isTestUploadMode()
                ) {
                    return $this->success(
                        'Upload test OK',
                        [
                            'file' => basename(
                                $uploadData->destinationPath,
                            ),
                        ],
                    );
                }

                try {

                    $inserted =
                        $this->mangaRepository
                            ->insert([
                                'thumbnail' => $uploadData->thumbnailPath,
                                'extension' => $uploadData->extension,
                                'slug' => $dto->slug,
                                'livre' => $dto->livre,
                                'editeur' => $dto->editeur,
                                'numero' => $dto->numero,
                                'statut' => $dto->statut,

                                'jacquette' => 1,
                                'livre_note' => 1,
                                'note' => 2,

                                'commentaire' => $dto->commentaire,
                            ]);

                    $failure =
                        $this->writeFailed(
                            $inserted,
                            'Insertion manga',
                            $dto->slug,
                            $dto->numero,
                            'Erreur lors de l’enregistrement',
                        );

                    if ($failure !== null) {

                        $this->uploadService
                            ->removeFile(
                                $uploadData->destinationPath,
                            );

                        return $failure;
                    }

                    $this->clearCache();

                    return $this->success(
                        'Manga ajouté avec succès',
                    );

                } catch (Throwable $exception) {

                    $this->uploadService
                        ->removeFile(
                            $uploadData->destinationPath,
                        );

                    throw $exception;
                }
            },
        );
    }

    public function update(
        string $slug,
        int $numero,
        MangaUpdateDTO $dto,
    ): ServiceResult {

        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto,
            ): ServiceResult {

                $updated =
                    $this->mangaRepository
                        ->updateManga(
                            $slug,
                            $numero,
                            $dto->editeur,
                            $dto->statut,
                            $dto->jacquette,
                            $dto->livreNote,
                            $dto->commentaire,
                        );

                $failure =
                    $this->writeFailed(
                        $updated,
                        'Update manga',
                        $slug,
                        $numero,
                        'Erreur lors de la mise à jour',
                    );

                if ($failure !== null) {
                    return $failure;
                }

                $this->clearCache();

                return $this->success(
                    'Manga mis à jour avec succès',
                );
            },
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

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto,
            ): ServiceResult {

                $updated =
                    $this->mangaRepository
                        ->updateNote(
                            $slug,
                            $numero,
                            $dto->jacquette,
                            $dto->livreNote,
                        );

                $failure =
                    $this->writeFailed(
                        $updated,
                        'Update note',
                        $slug,
                        $numero,
                        'Erreur lors de la mise à jour des notes',
                    );

                if ($failure !== null) {
                    return $failure;
                }

                $this->clearCache();

                $manga =
                    $this->mangaRepository
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
                        'notes' => new UpdateNoteData(
                            jacquette:
                                $dto->jacquette ?? 0,

                            livreNote:
                                $dto->livreNote ?? 0,

                            note:
                                $manga->note
                                ?? (
                                    ($dto->jacquette ?? 0)
                                    + ($dto->livreNote ?? 0)
                                ),
                        ),
                    ],
                );
            },
        );
    }

    public function updateReadStatus(
        string $slug,
        int $numero,
        int $readStatus,
    ): ServiceResult {

        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        if (! in_array($readStatus, [0, 1], true)) {
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
            ): ServiceResult {

                $updated =
                    $this->mangaRepository
                        ->updateReadStatus(
                            $slug,
                            $numero,
                            $readStatus === 1,
                        );

                $failure =
                    $this->writeFailed(
                        $updated,
                        'Update read status',
                        $slug,
                        $numero,
                        'Erreur lors de la mise à jour',
                    );

                if ($failure !== null) {
                    return $failure;
                }

                $this->clearCache();

                return $this->success(
                    $readStatus === 1
                        ? 'Manga marqué comme lu'
                        : 'Manga marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                    ],
                );
            },
        );
    }

    public function delete(
        string $slug,
        int $numero,
    ): ServiceResult {

        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
            ): ServiceResult {

                $manga =
                    $this->mangaRepository
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

                $deleted =
                    $this->mangaRepository
                        ->deleteBySlugAndNumero(
                            $slug,
                            $numero,
                        );

                $failure =
                    $this->writeFailed(
                        $deleted,
                        'Delete manga',
                        $slug,
                        $numero,
                        'Erreur lors de la suppression',
                    );

                if ($failure !== null) {
                    return $failure;
                }

                $this->removeThumbnail($manga);

                $this->clearCache();

                return $this->success(
                    'Manga supprimé avec succès',
                );
            },
        );
    }
}