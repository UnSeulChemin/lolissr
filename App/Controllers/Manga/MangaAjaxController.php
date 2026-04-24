<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

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
        if (!is_ajax()) {
            json([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }
    }

    protected function jsonErrorPayload(array $result): array
    {
        $payload = [
            'success' => false,
            'message' => (string) ($result['message'] ?? 'Une erreur est survenue'),
        ];

        if (isset($result['errors'])) {
            $payload['errors'] = $result['errors'];
        }

        if (isset($result['redirect'])) {
            $payload['redirect'] = $result['redirect'];
        }

        return $payload;
    }

    public function collectionPage(string $page = '1'): void
    {
        $this->ensureAjax();

        $data = $this->mangaReadService->collection($page);

        if ($data === null) {
            json([
                'success' => false,
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

        json(
            $this->mangaReadService->searchAjax($query)
        );
    }

    public function updateNote(string $slug, string $numero): void
    {
        $this->ensureAjax();

        $numero = (int) $numero;

        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            json([
                'success' => false,
                'message' => 'Manga introuvable',
            ], 404);
        }

        if ($slug !== $data['canonicalSlug']) {
            json([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => $this->basePath
                    . 'manga/modifier/'
                    . rawurlencode($data['canonicalSlug'])
                    . '/'
                    . $numero,
            ], 409);
        }

        $request = new MangaUpdateNoteRequest();

        if ($request->fails()) {
            json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $request->errors(),
            ], 422);
        }

        $post = $request->data() ?? [];

        $result = $this->mangaWriteService->updateNote(
            $data['canonicalSlug'],
            $numero,
            $post
        );

        json($result, (int) ($result['status'] ?? 200));
    }

    public function delete(string $slug, string $numero): void
    {
        $this->ensureAjax();

        $result = $this->mangaWriteService->delete(
            $slug,
            (int) $numero
        );

        if (!$result['success']) {
            json(
                $this->jsonErrorPayload($result),
                (int) ($result['status'] ?? 500)
            );
        }

        $redirectSlug = (string) ($result['canonicalSlug'] ?? $slug);

        json([
            'success' => true,
            'message' => $result['message'],
            'redirect' => $this->basePath
                . 'manga/serie/'
                . rawurlencode($redirectSlug),
        ]);
    }
}