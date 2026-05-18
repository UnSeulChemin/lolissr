<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Core\Application\App;
use App\Core\Http\Request;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

final class MangaAjaxController extends Controller
{
    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        protected Request $request
    ) {
        parent::__construct();
    }

    private function ensureAjax(Request $request): void
    {
        if ($request->isAjax()) {
            return;
        }

        $userAgent = $request->userAgent();

        if (
            App::isTesting()
            && str_contains(
                $userAgent,
                'LoliSSR-TestRunner'
            )
        ) {
            return;
        }

        $this->json([
            'success' => false,
            'message' => 'Requête AJAX requise',
        ], 400);
    }

    private function error(
        array $payload,
        int $status = 400
    ): void {
        $this->json([
            'success' => false,
            ...$payload,
        ], $status);
    }

    public function seriesPage(
        Request $request,
        string $page = '1'
    ): void {
        $this->ensureAjax($request);

        $data = $this->mangaReadService->series(
            $page
        );

        if ($data === null) {
            $this->error([
                'message' => 'Page introuvable',
            ], 404);
        }

        $this->renderPartial(
            'manga/partials/series_ajax',
            [
                'mangas' => $data['mangas'],
                'compteur' => $data['compteur'],
                'currentPage' => $data['currentPage'],
                'slugFilter' => null,
            ]
        );
    }

    public function search(
        Request $request,
        string $query = ''
    ): void {
        $this->ensureAjax($request);

        $this->json(
            $this->mangaReadService->searchAjax(
                $query
            )
        );
    }

    public function updateNote(
        MangaUpdateNoteRequest $request,
        string $slug,
        string $numero
    ): void {
        $this->ensureAjax($this->request);

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $numero = (int) $numero;

        $data = $this->mangaReadService->one(
            $slug,
            $numero
        );

        if ($data === null) {
            $this->error([
                'message' => 'Manga introuvable',
            ], 404);
        }

        $result = $this->mangaWriteService->updateNote(
            $data['canonicalSlug'],
            $numero,
            $request->dto()
        );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            ...$result->data,
        ], $result->status);
    }

    public function updateLu(
        Request $request,
        string $slug,
        string $numero
    ): void {
        $this->ensureAjax($request);

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $numero = (int) $numero;

        $data = $this->mangaReadService->one(
            $slug,
            $numero
        );

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
                    . rawurlencode(
                        $data['canonicalSlug']
                    )
                    . '/'
                    . $numero,
            ], 409);
        }

        $result = $this->mangaWriteService->updateLu(
            $data['canonicalSlug'],
            $numero,
            $request->integer('lu', 0)
        );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            'lu' => $result->data['lu'] ?? null,
        ], $result->status);
    }

    public function delete(
        Request $request,
        string $slug,
        string $numero
    ): void {
        $this->ensureAjax($request);

        if (!ctype_digit($numero)) {
            $this->error([
                'message' => 'Numéro invalide',
            ], 404);
        }

        $result = $this->mangaWriteService->delete(
            $slug,
            (int) $numero
        );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            ...$result->data,
        ], $result->status);
    }
}