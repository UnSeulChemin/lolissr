<?php
declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    private const AJAX_PATH = 'manga/ajax';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function ensureAjax(): void
    {
        if ($this->isAjax() || App::isTesting()) return;
        throw new BaseHttpException('Requête AJAX requise', 400);
    }

    // -----------------------------
    // AJAX Search
    // -----------------------------
    public function search(string $query = ''): never
    {
        $this->ensureAjax();

        try {
            $searchData = $this->mangaReadService->search($query);
            $results = $searchData->mangas ?? [];

            $this->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                ],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // -----------------------------
    // AJAX Series Page
    // -----------------------------
    public function seriesPage(string|int $page = 1): void
    {
        $this->ensureAjax();
        $page = max(1, (int) $page);
        $data = $this->mangaReadService->series($page);

        if ($data === null) throw new NotFoundException('Page introuvable');

        $this->renderPartial(
            'manga/partials/series_page_ajax',
            [
                'mangas' => $data->mangas,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->compteur,
                'slugFilter' => $data->slugFilter,
                'isSerieView' => $data->slugFilter !== null,
                'baseUri' => $this->baseUri,
            ]
        );
    }

    // -----------------------------
    // Update note
    // -----------------------------
    public function updateNote(MangaUpdateNoteRequest $request, string $slug, int $numero): never
    {
        $this->ensureAjax();
        $data = $this->resolveMangaOrFail('update-note', $slug, $numero);

        if ($request->fails()) throw new ValidationException($request->errors());

        $result = $this->mangaWriteService->updateNote($data->canonicalSlug, $numero, $request->dto());
        $this->json($result->toArray(), $result->status);
    }

    // -----------------------------
    // Update read status
    // -----------------------------
    public function updateReadStatus(string $slug, int $numero): never
    {
        $this->ensureAjax();
        $data = $this->resolveMangaOrFail('update-read-status', $slug, $numero);

        $result = $this->mangaWriteService->updateReadStatus(
            $data->canonicalSlug,
            $numero,
            $this->request->integer('readStatus', 0)
        );
        $this->json($result->toArray(), $result->status);
    }

    // -----------------------------
    // Delete manga
    // -----------------------------
    public function delete(string $slug, int $numero): never
    {
        $this->ensureAjax();
        $data = $this->resolveMangaOrFail('delete', $slug, $numero);

        $result = $this->mangaWriteService->delete($data->canonicalSlug, $numero);
        $this->json($result->toArray(), $result->status);
    }

    // -----------------------------
    // Resolve manga or fail
    // -----------------------------
    private function resolveMangaOrFail(string $action, string $slug, int $numero): object
    {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) throw new NotFoundException('Manga introuvable');

        if ($slug !== $data->canonicalSlug) {
            throw new BaseHttpException(
                message: 'URL non canonique',
                statusCode: 409,
                data: [
                    'redirect' => sprintf('%s/%s/%s/%d', $this->baseUri, self::AJAX_PATH, rawurlencode($data->canonicalSlug), $numero)
                ]
            );
        }

        return $data;
    }
}