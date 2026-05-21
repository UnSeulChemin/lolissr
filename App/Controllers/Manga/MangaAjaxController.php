<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function ensureAjax(): void
    {
        if ($this->request->isAjax()) {
            return;
        }

        if (
            App::isTesting()
            && str_contains(
                $this->request->userAgent(),
                'LoliSSR-TestRunner',
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
        string $message,
        int $status = 400,
        ?string $redirect = null,
    ): never {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($redirect !== null) {
            $response['redirect'] = $redirect;
        }

        $this->json(
            $response,
            $status,
        );
    }

    private function canonicalRedirect(
        string $action,
        string $slug,
        int $numero,
    ): string {
        return $this->basePath
            . 'manga/ajax/'
            . $action
            . '/'
            . rawurlencode($slug)
            . '/'
            . $numero;
    }

    public function seriesPage(
        string $page = '1',
    ): never {
        $this->ensureAjax();

        $data = $this->mangaReadService
            ->series($page);

        if ($data === null) {
            $this->error(
                'Page introuvable',
                404,
            );
        }

        $this->renderPartial(
            'manga/partials/series_ajax',
            [
                'mangas' => $data->mangas,
                'compteur' => $data->compteur,
                'currentPage' => $data->currentPage,
                'slugFilter' => $data->slugFilter,
            ],
        );
    }

    public function search(
        string $query = '',
    ): never {
        $this->ensureAjax();

        $results = $this->mangaReadService
            ->searchAjax($query);

        $this->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function updateNote(
        MangaUpdateNoteRequest $request,
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->error(
                'Manga introuvable',
                404,
            );
        }

        if ($slug !== $data->canonicalSlug) {
            $this->error(
                'URL non canonique',
                409,
                $this->canonicalRedirect(
                    'update-note',
                    $data->canonicalSlug,
                    $numero,
                ),
            );
        }

        if ($request->fails()) {
            $this->json([
                'success' => false,
                'message' => 'Formulaire invalide',
                'errors' => $request->errors(),
            ], 422);
        }

        $result = $this->mangaWriteService
            ->updateNote(
                $data->canonicalSlug,
                $numero,
                $request->dto(),
            );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            ...$result->data,
        ], $result->status);
    }

    public function updateLu(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->error(
                'Manga introuvable',
                404,
            );
        }

        if ($slug !== $data->canonicalSlug) {
            $this->error(
                'URL non canonique',
                409,
                $this->canonicalRedirect(
                    'update-lu',
                    $data->canonicalSlug,
                    $numero,
                ),
            );
        }

        $result = $this->mangaWriteService
            ->updateLu(
                $data->canonicalSlug,
                $numero,
                $this->request->integer(
                    'lu',
                    0,
                ),
            );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            'lu' => $result->lu,
        ], $result->status);
    }

    public function delete(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->error(
                'Manga introuvable',
                404,
            );
        }

        if ($slug !== $data->canonicalSlug) {
            $this->error(
                'URL non canonique',
                409,
                $this->canonicalRedirect(
                    'delete',
                    $data->canonicalSlug,
                    $numero,
                ),
            );
        }

        $result = $this->mangaWriteService
            ->delete(
                $data->canonicalSlug,
                $numero,
            );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
        ], $result->status);
    }
}
