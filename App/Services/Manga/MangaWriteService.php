<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateDTO;
use App\DTO\Manga\Inputs\MangaUpdateDTO;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Responses\UpdateNoteData;
use App\DTO\Upload\UploadThumbnailData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaStatsRepository;
use App\Services\UploadService;
use App\Services\User\UserLevelService;

use Framework\Cache\Cache;
use Framework\Config\UploadConfig;
use Framework\Database\Database;
use Framework\Support\Logger;

use Throwable;

final readonly class MangaWriteService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaStatsRepository $mangaStatsRepository,
        private UploadService $uploadService,
        private Database $database,
        private UserLevelService $userLevelService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $files
     */
    public function create(MangaCreateDTO $dto, array $files): ServiceResult
    {
        $existingManga = $this->mangaRepository->findOneBySlugAndNumero($dto->slug, $dto->numero);

        if ($existingManga !== null)
        {
            return $this->error('Ce manga existe déjà', 409);
        }

        return $this->database->transaction(
            function () use ($dto, $files): ServiceResult
            {
                $upload = $this->uploadService->uploadThumbnail(
                    $dto->livre,
                    $dto->numero,
                    UploadConfig::thumbnailDirectory('manga'),
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
                    $inserted = $this->mangaRepository->insert([
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

                    $failure = $this->writeFailed(
                        $inserted,
                        'Insertion manga',
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

                    return $this->success('Manga ajouté avec succès');
                }
                catch (Throwable $exception)
                {
                    $this->rollbackUpload($uploadData);

                    throw $exception;
                }
            }
        );
    }

    public function update(string $slug, int $numero, MangaUpdateDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto
            ): ServiceResult
            {
                $updated = $this->mangaRepository->updateManga(
                    $slug,
                    $numero,
                    $dto->editeur,
                    $dto->statut,
                    $dto->jacquette,
                    $dto->livreNote,
                    $dto->commentaire
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update manga',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->clearCache();

                return $this->success('Manga mis à jour avec succès');
            }
        );
    }

    public function updateNote(string $slug, int $numero, MangaUpdateNoteDTO $dto): ServiceResult
    {
        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $dto
            ): ServiceResult
            {
                $updated = $this->mangaRepository->updateNote(
                    $slug,
                    $numero,
                    $dto->jacquette,
                    $dto->livreNote
                );

                $failure = $this->writeFailed(
                    $updated,
                    'Update note',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour des notes'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->clearCache();

                $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

                if ($manga === null)
                {
                    return $this->error('Manga introuvable', 404);
                }

                return $this->success(
                    'Notes mises à jour',
                    [
                        'notes' => new UpdateNoteData(
                            jacquette: $dto->jacquette ?? 0,
                            livreNote: $dto->livreNote ?? 0,
                            note: $manga->note
                                ?? (($dto->jacquette ?? 0) + ($dto->livreNote ?? 0))
                        ),
                    ]
                );
            }
        );
    }

    public function updateReadStatus(string $slug, int $numero, int $readStatus): ServiceResult
    {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error('Statut de lecture invalide', 422);
        }

        return $this->database->transaction(
            function () use (
                $slug,
                $numero,
                $readStatus
            ): ServiceResult
            {
                $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

                if ($manga === null)
                {
                    return $this->error('Manga introuvable', 404);
                }

                $updated = $this->mangaRepository->updateReadStatus($slug, $numero, $readStatus === 1);

                $failure = $this->writeFailed(
                    $updated,
                    'Update read status',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                if (! $manga->lu && $readStatus === 1 && ! $manga->xp_read_rewarded)
                {
                    $this->rewardReadXp($manga, $slug);
                }

                $this->clearCache();

                return $this->success(
                    $readStatus === 1
                        ? 'Manga marqué comme lu'
                        : 'Manga marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                    ]
                );
            }
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
                $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

                if ($manga === null)
                {
                    return $this->error('Manga introuvable', 404);
                }

                $deleted = $this->mangaRepository->deleteBySlugAndNumero($slug, $numero);

                $failure = $this->writeFailed(
                    $deleted,
                    'Delete manga',
                    $slug,
                    $numero,
                    'Erreur lors de la suppression'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                $this->removeThumbnail($manga);

                $this->clearCache();

                return $this->success('Manga supprimé avec succès');
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    private function rewardReadXp(Manga $manga, string $slug): void
    {
        $user = user();

        if ($user === null)
        {
            return;
        }

        $this->userLevelService->addXp($user, UserXp::READ_TOME);

        if ($this->mangaStatsRepository->isSeriesCompleted($slug) && ! $this->mangaRepository->isSeriesRewarded($slug))
        {
            $this->userLevelService->addXp($user, UserXp::COMPLETE_SERIES);

            $this->mangaRepository->markSeriesRewardedBySlug($slug);
        }

        $this->mangaRepository->markXpRewarded($manga->id);
    }

    /*
    |--------------------------------------------------------------------------
    | FILES
    |--------------------------------------------------------------------------
    */

    private function removeThumbnail(Manga $manga): void
    {
        if ($manga->thumbnail === '' || $manga->extension === '')
        {
            return;
        }

        $path = UploadConfig::thumbnailDirectory('manga') . $manga->thumbnail . '.' . $manga->extension;

        $this->uploadService->removeFile($path);
    }

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

    private function logFailure(string $action, string $slug, int $numero): void
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
