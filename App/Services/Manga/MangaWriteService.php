<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\DTO\Manga\MangaCreateDTO;
use App\DTO\Manga\MangaUpdateDTO;
use App\DTO\Manga\MangaUpdateNoteDTO;
use App\Repositories\Manga\MangaRepository;
use App\Services\UploadService;

final class MangaWriteService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository,
        private readonly UploadService $uploadService,
        private readonly MangaValidatorService $validatorService,
        private readonly MangaCacheService $cacheService
    ) {}

    private function isReadOnlyMode(): bool
    {
        return App::isReadOnly();
    }

    private function blockedWriteResponse(): array
    {
        return [
            'success' => false,
            'status' => 403,
            'message' => 'Écriture en base désactivée en mode test',
        ];
    }

    public function create(array $post, array $files): array
    {
        $validator = $this->validatorService->makeCreateValidator($post, $files);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return [
                'success' => false,
                'status' => 422,
                'message' => $this->validatorService->firstErrorMessage($errors),
                'errors' => $errors,
            ];
        }

        $dto = MangaCreateDTO::fromPost($post);

        /*
         * En testing :
         * - upload test autorisé
         * - aucune écriture DB
         */
        if ($this->isReadOnlyMode() && !$this->uploadService->isTestUploadMode()) {
            return $this->blockedWriteResponse();
        }

        if (
            !$this->uploadService->isTestUploadMode()
            && $this->mangaRepository->findOneBySlugAndNumero($dto->slug, $dto->numero)
        ) {
            return [
                'success' => false,
                'status' => 409,
                'message' => 'Ce manga existe déjà',
            ];
        }

        $upload = $this->uploadService->uploadThumbnail(
            $dto->livre,
            $dto->numero,
            $files,
            'image'
        );

        if (!$upload['success']) {
            return [
                'success' => false,
                'status' => (int) $upload['status'],
                'message' => (string) $upload['message'],
            ];
        }

        if ($this->uploadService->isTestUploadMode()) {
            return [
                'success' => true,
                'status' => 200,
                'message' => 'Upload test OK',
                'file' => basename((string) $upload['destination']),
            ];
        }

        $insert = $this->mangaRepository->insert([
            'thumbnail' => $upload['thumbnail'],
            'extension' => $upload['extension'],
            'slug' => $dto->slug,
            'livre' => $dto->livre,
            'numero' => $dto->numero,
            'jacquette' => null,
            'livre_note' => null,
            'commentaire' => $dto->commentaire,
        ]);

        if (!$insert) {
            $this->uploadService->removeFileIfExists((string) $upload['destination']);

            Logger::error("Insertion manga échouée slug={$dto->slug} numero={$dto->numero}");

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de l’enregistrement',
            ];
        }

        $this->cacheService->clear();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga ajouté avec succès',
        ];
    }

    public function update(string $slug, int $numero, array $post, array $files): array
    {
        $validator = $this->validatorService->makeUpdateValidator($post, $files);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return [
                'success' => false,
                'status' => 422,
                'message' => $this->validatorService->firstErrorMessage($errors),
                'errors' => $errors,
            ];
        }

        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $dto = MangaUpdateDTO::fromPost($post);

        $updated = $this->mangaRepository->updateManga(
            $slug,
            $numero,
            $dto->jacquette,
            $dto->livreNote,
            $dto->commentaire
        );

        if (!$updated) {
            Logger::error("Update manga échoué slug=$slug numero=$numero");

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour',
            ];
        }

        $this->cacheService->clear();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga mis à jour avec succès',
        ];
    }

    public function updateNote(string $slug, int $numero, array $post): array
    {
        $validator = $this->validatorService->makeUpdateNoteValidator($post);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return [
                'success' => false,
                'status' => 422,
                'message' => $this->validatorService->firstErrorMessage($errors),
                'errors' => $errors,
            ];
        }

        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $dto = MangaUpdateNoteDTO::fromPost($post);

        $updated = $this->mangaRepository->updateNote(
            $slug,
            $numero,
            $dto->jacquette,
            $dto->livreNote
        );

        if (!$updated) {
            Logger::error("Update note échoué slug=$slug numero=$numero");

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour',
            ];
        }

        $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

        if ($manga === false) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Manga introuvable après mise à jour',
            ];
        }

        $this->cacheService->clear();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Notes mises à jour',
            'jacquette' => $manga->jacquette,
            'livre_note' => $manga->livre_note,
            'note' => $manga->note,
        ];
    }

    public function updateLu(string $slug, int $numero, array $post): array
    {
        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $lu = (int) ($post['lu'] ?? 0);

        if (!in_array($lu, [0, 1], true)) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Statut de lecture invalide',
            ];
        }

        $updated = $this->mangaRepository->updateLu(
            $slug,
            $numero,
            $lu === 1
        );

        if (!$updated) {
            Logger::error("Update lu échoué slug=$slug numero=$numero");

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour',
            ];
        }

        $this->cacheService->clear();

        return [
            'success' => true,
            'status' => 200,
            'message' => $lu === 1 ? 'Manga marqué comme lu' : 'Manga marqué comme non lu',
            'lu' => $lu,
        ];
    }

    public function delete(string $slug, int $numero): array
    {
        if ($this->isReadOnlyMode()) {
            return $this->blockedWriteResponse();
        }

        $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

        if ($manga === false) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Manga introuvable',
            ];
        }

        $imagePath = UploadConfig::mangaThumbnailDirectory()
            . $manga->thumbnail
            . '.'
            . $manga->extension;

        $deleted = $this->mangaRepository->deleteBySlugAndNumero($slug, $numero);

        if (!$deleted) {
            Logger::error("Delete manga échoué slug=$slug numero=$numero");

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la suppression',
            ];
        }

        $this->uploadService->removeFileIfExists($imagePath);
        $this->cacheService->clear();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga supprimé avec succès',
            'canonicalSlug' => $manga->slug,
        ];
    }
}