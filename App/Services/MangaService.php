<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Config\UploadConfig;
use App\Core\Support\Logger;
use App\Core\Support\Str;
use App\Repositories\MangaRepository;

class MangaService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository = new MangaRepository(),
        private readonly UploadService $uploadService = new UploadService(),
        private readonly MangaValidatorService $validatorService = new MangaValidatorService()
    ) {
    }

    /**
     * Retourne le repository manga.
     */
    public function repository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    /**
     * Retourne l’upload service.
     */
    public function uploadService(): UploadService
    {
        return $this->uploadService;
    }

    /**
     * Retourne le validator service.
     */
    public function validatorService(): MangaValidatorService
    {
        return $this->validatorService;
    }

    /**
     * Indique si les écritures doivent être bloquées.
     */
    private function isReadOnlyMode(): bool
    {
        return App::isReadOnly();
    }

    /**
     * Retourne la réponse standard de blocage d’écriture.
     *
     * @return array{
     *     success: bool,
     *     status: int,
     *     message: string
     * }
     */
    private function blockedWriteResponse(): array
    {
        return [
            'success' => false,
            'status' => 403,
            'message' => 'Écriture en base désactivée en mode test'
        ];
    }

    /**
     * Convertit une note postée.
     * Retourne null si vide ou invalide.
     */
    public function normalizePostedNote(null|string|int $value): ?int
    {
        if ($value === null)
        {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '')
        {
            return null;
        }

        $value = (int) $value;

        if ($value < 1 || $value > 5)
        {
            return null;
        }

        return $value;
    }

    /**
     * Retourne une chaîne postée nettoyée.
     */
    private function postString(array $post, string $key): string
    {
        return trim((string) ($post[$key] ?? ''));
    }

    /**
     * Retourne un entier posté.
     */
    private function postInt(array $post, string $key): int
    {
        return (int) ($post[$key] ?? 0);
    }

    /**
     * Retourne une chaîne nullable postée.
     */
    private function postNullableString(array $post, string $key): ?string
    {
        if (!array_key_exists($key, $post))
        {
            return null;
        }

        $value = trim((string) $post[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * Retourne les données normalisées d’ajout.
     *
     * @return array{
     *     livre: string,
     *     slug: string,
     *     numero: int,
     *     commentaire: ?string
     * }
     */
    private function normalizedCreateData(array $post): array
    {
        $livre = $this->postString($post, 'livre');
        $slug = Str::slug($this->postString($post, 'slug'));
        $numero = $this->postInt($post, 'numero');
        $commentaire = Str::nullableTrim(
            $this->postNullableString($post, 'commentaire')
        );

        return [
            'livre' => $livre,
            'slug' => $slug,
            'numero' => $numero,
            'commentaire' => $commentaire
        ];
    }

    /**
     * Retourne les données normalisées de modification.
     *
     * @return array{
     *     jacquette: ?int,
     *     livre_note: ?int,
     *     commentaire: ?string
     * }
     */
    private function normalizedUpdateData(array $post): array
    {
        $jacquette = $this->normalizePostedNote($post['jacquette'] ?? null);
        $livreNote = $this->normalizePostedNote($post['livre_note'] ?? null);
        $commentaire = Str::nullableTrim(
            $this->postNullableString($post, 'commentaire')
        );

        return [
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
            'commentaire' => $commentaire
        ];
    }

    /**
     * Traite la création d’un manga.
     *
     * @return array{
     *     success: bool,
     *     status: int,
     *     message: string,
     *     errors?: array<string, mixed>,
     *     file?: string
     * }
     */
    public function create(array $post, array $files): array
    {
        $validator = $this->validatorService->makeCreateValidator($post, $files);

        if ($validator->fails())
        {
            $errors = $validator->errors();

            return [
                'success' => false,
                'status' => 422,
                'message' => $this->validatorService->firstErrorMessage($errors),
                'errors' => $errors
            ];
        }

        $data = $this->normalizedCreateData($post);

        if (
            !$this->uploadService->isTestUploadMode()
            && $this->mangaRepository->findOneBySlugAndNumero(
                $data['slug'],
                $data['numero']
            )
        )
        {
            return [
                'success' => false,
                'status' => 409,
                'message' => 'Ce manga existe déjà'
            ];
        }

        $upload = $this->uploadService->uploadThumbnail(
            $data['livre'],
            $data['numero'],
            $files,
            'image'
        );

        if (!$upload['success'])
        {
            return [
                'success' => false,
                'status' => (int) $upload['status'],
                'message' => (string) $upload['message']
            ];
        }

        if ($this->uploadService->isTestUploadMode())
        {
            return [
                'success' => true,
                'status' => 200,
                'message' => 'Upload test OK (aucune écriture en base)',
                'file' => basename((string) $upload['destination'])
            ];
        }

        if ($this->isReadOnlyMode())
        {
            $this->uploadService->removeFileIfExists((string) $upload['destination']);

            return $this->blockedWriteResponse();
        }

        $insert = $this->mangaRepository->insert([
            'thumbnail' => $upload['thumbnail'],
            'extension' => $upload['extension'],
            'slug' => $data['slug'],
            'livre' => $data['livre'],
            'numero' => $data['numero'],
            'jacquette' => null,
            'livre_note' => null,
            'commentaire' => $data['commentaire']
        ]);

        if (!$insert)
        {
            $this->uploadService->removeFileIfExists((string) $upload['destination']);

            Logger::error(
                'Insertion manga échouée après upload. slug='
                . $data['slug']
                . ', numero='
                . $data['numero']
            );

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de l’enregistrement du manga'
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga ajouté avec succès'
        ];
    }

    /**
     * Traite la mise à jour d’un manga.
     *
     * @return array{
     *     success: bool,
     *     status: int,
     *     message: string,
     *     errors?: array<string, mixed>
     * }
     */
    public function update(string $slug, int $numero, array $post, array $files): array
    {
        $validator = $this->validatorService->makeUpdateValidator($post, $files);

        if ($validator->fails())
        {
            $errors = $validator->errors();

            return [
                'success' => false,
                'status' => 422,
                'message' => $this->validatorService->firstErrorMessage($errors),
                'errors' => $errors
            ];
        }

        if ($this->isReadOnlyMode())
        {
            return $this->blockedWriteResponse();
        }

        $data = $this->normalizedUpdateData($post);

        $updated = $this->mangaRepository->updateManga(
            $slug,
            $numero,
            $data['jacquette'],
            $data['livre_note'],
            $data['commentaire']
        );

        if (!$updated)
        {
            Logger::error(
                'Échec update manga. slug='
                . $slug
                . ', numero='
                . $numero
            );

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour'
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga mis à jour avec succès'
        ];
    }

    /**
     * Traite la suppression d’un manga.
     *
     * @return array{
     *     success: bool,
     *     status: int,
     *     message: string
     * }
     */
    public function delete(string $slug, int $numero): array
    {
        if ($this->isReadOnlyMode())
        {
            return $this->blockedWriteResponse();
        }

        $manga = $this->mangaRepository->findOneBySlugAndNumero($slug, $numero);

        if ($manga === false)
        {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Manga introuvable'
            ];
        }

        $imagePath = UploadConfig::mangaThumbnailDirectory()
            . $manga->thumbnail
            . '.'
            . $manga->extension;

        $deleted = $this->mangaRepository->deleteBySlugAndNumero($slug, $numero);

        if (!$deleted)
        {
            Logger::error(
                'Échec suppression manga. slug='
                . $slug
                . ', numero='
                . $numero
            );

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erreur lors de la suppression'
            ];
        }

        $this->uploadService->removeFileIfExists($imagePath);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Manga supprimé avec succès'
        ];
    }
}