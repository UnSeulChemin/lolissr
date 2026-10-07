<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Cache\Home\DashboardCache;
use App\Constants\Profile\XpRewards;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaCreateData;
use App\DTO\Manga\Inputs\MangaUpdateData;
use App\DTO\Manga\Inputs\MangaUpdateNoteData;
use App\DTO\Media\UploadThumbnailData;
use App\Repositories\Manga\MangaRepository;
use App\Services\Collections\CollectionCreationService;
use App\Services\Media\ThumbnailUploadService;

use Framework\Database\Database;
use Framework\Logging\Logger;

final readonly class MangaWriteService
{
    use \App\Services\Collections\Concerns\BuildsCollectionWriteResults;

    public function __construct(
        private MangaRepository $mangaRepository,
        private ThumbnailUploadService $thumbnailUploadService,
        private Database $database,
        private MangaXpRewardService $mangaXpRewardService,
        private CollectionCreationService $creationService,
        private DashboardCache $dashboardCache
    )
    {
    }

    // --------------------------------------------------------------------------
    // CRÉATION
    // --------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $files
     */
    public function create(MangaCreateData $dto, array $files): ServiceResult
    {
        if ($this->mangaRepository->findRecordBySlugAndNumero($dto->slug, $dto->numero) !== null)
        {
            return $this->error('Ce manga existe déjà', 409);
        }

        $result = $this->creationService->create(
            'manga',
            $dto->livre,
            $dto->numero,
            $files,
            function (UploadThumbnailData $upload) use ($dto): ServiceResult
            {
                $this->mangaRepository->lockSeries($dto->slug);
                $failure = $this->createManga($dto, $upload);

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Manga ajouté avec succès');
            },
            'Ce manga existe déjà'
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // --------------------------------------------------------------------------
    // MISE À JOUR
    // --------------------------------------------------------------------------

    public function acquireRelease(string $slug, int $numero, UpcomingMangaService $releases): ServiceResult
    {
        if (user() === null) return $this->error('Connexion requise', 401);
        if ($numero < 1 || $numero > 999) return $this->error('Numéro invalide', 422);
        if ($this->mangaRepository->findRecordBySlugAndNumero($slug, $numero) !== null) return $this->error('Ce manga existe déjà', 409);
        $release = null;
        foreach ($releases->forSeries($slug) as $candidate)
            if ($candidate->number === $numero)
            { $release = $candidate; break; }
        if ($release === null) return $this->error('Tome introuvable', 404);
        $series = null;
        foreach ($this->mangaRepository->seriesForCreate() as $candidate)
            if ($candidate['slug'] === $slug)
            { $series = $candidate; break; }
        if ($series === null) return $this->error('Série introuvable', 404);
        // Resolve only a validated catalog ID; never accept an image path from the client.
        $id = basename($release->sourceUrl);
        if (preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) return $this->error('Couverture invalide', 422);
        $source = base_path('public/images/manga/upcoming/' . $id . '.jpg');
        $bytes = is_file($source) ? file_get_contents($source) : false;
        $size = is_string($bytes) ? @getimagesizefromstring($bytes) : false;
        if (!is_string($bytes) || strlen($bytes) > 5 * 1024 * 1024 || $size === false || $size[2] !== IMAGETYPE_JPEG || $size[0] * $size[1] > 10000000)
            return $this->error('Couverture indisponible : utilise le formulaire d’ajout', 422);
        $directory = \App\Support\Media\ThumbnailDirectory::resolve('manga');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Cannot create thumbnail directory.');
        $baseName = \App\Support\Media\ThumbnailName::generate($series['livre'], $numero);
        if ($baseName === '') return $this->error('Nom de couverture invalide', 422);
        $thumbnail = mb_strcut($baseName, 0, 180, 'UTF-8');
        $destination = $directory . $thumbnail . '.webp';
        $handle = @fopen($destination, 'xb');
        if ($handle === false && file_exists($destination))
        {
            // Keep a readable series/volume prefix while isolating another owner's file.
            $thumbnail .= '-' . bin2hex(random_bytes(16));
            $destination = $directory . $thumbnail . '.webp';
            $handle = @fopen($destination, 'xb');
        }
        if ($handle === false) throw new \RuntimeException('Cannot stage release cover.');
        $committed = false;
        $upload = new UploadThumbnailData($thumbnail, 'webp', $destination);
        try
        {
            try
            {
                $image = @imagecreatefromstring($bytes);
                if ($image === false) throw new \RuntimeException('Cannot decode release cover.');
                try
                {
                    if (!imagewebp($image, $handle, 85)) throw new \RuntimeException('Cannot convert release cover to WebP.');
                }
                finally
                { imagedestroy($image); }
            }
            finally
            { fclose($handle); }
            $converted = @getimagesize($destination);
            if ($converted === false || $converted[2] !== IMAGETYPE_WEBP) throw new \RuntimeException('Invalid converted release cover.');
            $dto = new MangaCreateData($slug, $series['livre'], $series['editeur'] ?? '', $numero, $series['statut'], null);
            $result = $this->database->transaction(function () use ($dto, $upload): ServiceResult
            {
                $this->mangaRepository->lockSeries($dto->slug);
                if (!$this->mangaRepository->seriesExists($dto->slug)) return $this->error('Série introuvable', 404);
                if ($this->mangaRepository->findRecordBySlugAndNumero($dto->slug, $dto->numero) !== null) return $this->error('Ce manga existe déjà', 409);
                return $this->createManga($dto, $upload) ?? $this->success('Tome ajouté à ta collection');
            });
            $committed = $result->success;
            if ($committed) $this->forgetDashboardCache();
            return $result;
        }
        catch (\PDOException $error)
        {
            if ($error->getCode() === '23000' && ($error->errorInfo[1] ?? null) === 1062) return $this->error('Ce manga existe déjà', 409);
            throw $error;
        }
        finally
        {
            if (!$committed) $this->thumbnailUploadService->rollback($upload);
        }
    }

    public function update(string $slug, int $numero, MangaUpdateData $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
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

                $this->mangaXpRewardService->rewardSeriesAchievements();

                return $this->success('Manga mis à jour avec succès');
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    public function updateNote(string $slug, int $numero, MangaUpdateNoteData $dto): ServiceResult
    {
        $result = $this->database->transaction(
            function () use ($slug, $numero, $dto): ServiceResult
            {
                $notes = $this->mangaRepository->updateNote($slug, $numero, $dto->jacquette, $dto->livreNote,
                    $dto->updateJacquette, $dto->updateLivreNote);

                $failure = $this->writeFailed(
                    $notes !== false,
                    'Update note',
                    $slug,
                    $numero,
                    'Erreur lors de la mise à jour des notes'
                );

                if ($failure !== null)
                {
                    return $failure;
                }

                return $this->success('Notes mises à jour', ['notes' => $notes]);
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // --------------------------------------------------------------------------
    // MISE À JOUR DU STATUT DE LECTURE
    // --------------------------------------------------------------------------

    public function updateReadStatus(string $slug, int $numero, int $readStatus): ServiceResult
    {
        if (! in_array($readStatus, [0, 1], true))
        {
            return $this->error('Statut de lecture invalide', 422);
        }

        $result = $this->database->transaction(
            function () use ($slug, $numero, $readStatus): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
                $manga = $this->mangaRepository->findRecordBySlugAndNumero($slug, $numero);

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

                $xpEarned = false;
                $seriesXpEarned = false;

                if (! $manga->lu && $readStatus === 1)
                {
                    ['xpEarned' => $xpEarned, 'seriesXpEarned' => $seriesXpEarned] = $this->mangaXpRewardService->rewardRead($manga, $slug);
                }

                $user = user();

                return $this->success(
                    $readStatus === 1
                        ? 'Manga marqué comme lu'
                        : 'Manga marqué comme non lu',
                    [
                        'readStatus' => $readStatus,
                        'xpEarned' => $xpEarned,
                        'xpAmount' => $xpEarned ? XpRewards::READ_TOME : 0,
                        'seriesXpEarned' => $seriesXpEarned,
                        'level' => $user?->level,
                        'xp' => $user?->xp
                    ]
                );
            }
        );

        if ($result->success)
        {
            $this->forgetDashboardCache();
        }

        return $result;
    }

    // --------------------------------------------------------------------------
    // SUPPRESSION
    // --------------------------------------------------------------------------

    public function delete(string $slug, int $numero): ServiceResult
    {
        $manga = $this->mangaRepository->findRecordBySlugAndNumero($slug, $numero);

        if ($manga === null)
        {
            return $this->error('Manga introuvable', 404);
        }

        $result = $this->database->transaction(
            function () use ($slug, $manga): ServiceResult
            {
                $this->mangaRepository->lockSeries($slug);
                $deleted = $this->mangaRepository->deleteById($manga->id);

                if (!$deleted)
                {
                    return $this->error('Élément introuvable ou déjà supprimé', 404);
                }

                $this->mangaXpRewardService->rewardSeriesAchievements();

                return $this->success('Manga supprimé avec succès');
            }
        );

        if (! $result->success)
        {
            return $result;
        }

        if (! $this->thumbnailUploadService->remove($manga->thumbnail, $manga->extension, 'manga'))
        {
            Logger::warning(
                "Manga supprimé mais thumbnail non supprimée slug={$slug} numero={$numero}"
            );
        }

        $this->forgetDashboardCache();

        return $result;
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function createManga(MangaCreateData $dto, UploadThumbnailData $uploadData): ?ServiceResult
    {
        $inserted = $this->mangaRepository->insert([
            'thumbnail' => $uploadData->thumbnailPath,
            'extension' => $uploadData->extension,
            'slug' => $dto->slug,
            'livre' => $dto->livre,
            'editeur' => $dto->editeur,
            'numero' => $dto->numero,
            'statut' => $dto->statut,
            'jacquette' => $dto->jacquette,
            'livre_note' => $dto->livreNote,
            'note' => $dto->jacquette !== null && $dto->livreNote !== null
                ? $dto->jacquette + $dto->livreNote
                : null,
            'commentaire' => $dto->commentaire
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
            return $failure;
        }

        return null;
    }

    // --------------------------------------------------------------------------
    // CACHE
    // --------------------------------------------------------------------------

    private function forgetDashboardCache(): void
    {
        $this->dashboardCache->forget();
    }
}
