<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use App\Core\Application\App;

final class MangaAjaxController extends Controller
{
    protected MangaRepository $mangaRepository;
    protected MangaWriteService $mangaWriteService;
    protected MangaReadService $mangaReadService;

    public function __construct()
    {
        parent::__construct();

        $this->mangaRepository = app(MangaRepository::class);
        $this->mangaWriteService = app(MangaWriteService::class);
        $this->mangaReadService = app(MangaReadService::class);
    }

private function ensureAjax(): void
{
    if (is_ajax()) {
        return;
    }

    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

    if (
        \App\Core\Application\App::isTesting()
        && str_contains($userAgent, 'LoliSSR-TestRunner')
    ) {
        return;
    }

    json([
        'success' => false,
        'message' => 'Requête AJAX requise',
    ], 400);
}

    private function error(array $payload, int $status = 400): void
    {
        json(array_merge([
            'success' => false,
        ], $payload), $status);
    }

    public function collectionPage(string $page = '1'): void
    {
        $this->ensureAjax();

        $data = $this->mangaReadService->collection($page);

        if ($data === null) {
            $this->error([
                'message' => 'Page introuvable',
            ], 404);
        }

        $this->renderPartial('manga/partials/collection_ajax', [
            'mangas' => $data['mangas'],
            'compteur' => $data['compteur'],
            'currentPage' => $data['currentPage'],
            'slugFilter' => null,
        ]);
    }

    public function search(string $query = ''): void
    {
        $this->ensureAjax();

        json($this->mangaReadService->searchAjax($query));
    }

    public function updateNote(string $slug, string $numero): void
    {
        $this->ensureAjax();

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $numero = (int) $numero;

        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            $this->error([
                'message' => 'Manga introuvable',
            ], 404);
        }

        if ($slug !== $data['canonicalSlug']) {
            $this->error([
                'message' => 'URL non canonique',
                'redirect' => $this->basePath
                    . 'manga/ajax/update-note/'
                    . rawurlencode($data['canonicalSlug'])
                    . '/'
                    . $numero,
            ], 409);
        }

        $request = new MangaUpdateNoteRequest();

        if ($request->fails()) {
            $this->error([
                'message' => 'Erreur de validation',
                'errors' => $request->errors(),
            ], 422);
        }

        $result = $this->mangaWriteService->updateNote(
            $data['canonicalSlug'],
            $numero,
            $request->data()
        );

        json($result, (int) ($result['status'] ?? 200));
    }

    public function updateLu(string $slug, string $numero): void
    {
        $this->ensureAjax();

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $numero = (int) $numero;

        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            $this->error([
                'message' => 'Manga introuvable',
            ], 404);
        }

        if ($slug !== $data['canonicalSlug']) {
            $this->error([
                'message' => 'URL non canonique',
                'redirect' => $this->basePath
                    . 'manga/ajax/update-lu/'
                    . rawurlencode($data['canonicalSlug'])
                    . '/'
                    . $numero,
            ], 409);
        }

        $result = $this->mangaWriteService->updateLu(
            $data['canonicalSlug'],
            $numero,
            $_POST
        );

        json($result, (int) ($result['status'] ?? 200));
    }

    public function delete(string $slug, string $numero): void
    {
        $this->ensureAjax();

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $result = $this->mangaWriteService->delete($slug, (int) $numero);

        if (!$result['success']) {
            $this->error([
                'message' => (string) ($result['message'] ?? 'Une erreur est survenue'),
                'errors' => $result['errors'] ?? null,
                'redirect' => $result['redirect'] ?? null,
            ], (int) ($result['status'] ?? 500));
        }

        json([
            'success' => true,
            'message' => $result['message'],
            'redirect' => $this->basePath
                . 'manga/serie/'
                . rawurlencode((string) ($result['canonicalSlug'] ?? $slug)),
        ]);
    }
}