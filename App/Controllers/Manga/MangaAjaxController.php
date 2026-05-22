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
        Request $request
    ) {
        parent::__construct($request);
    }

    /**
     * Vérifie que la requête est bien AJAX ou test.
     */
    private function ensureAjax(): void
    {
        if ($this->isAjax() || App::isTesting()) {
            return;
        }

        throw new BaseHttpException(
            message: 'Requête AJAX requise',
            statusCode: 400
        );
    }

    /**
     * Retourne l'URL canonique pour redirection si nécessaire.
     */
    private function canonicalRedirect(string $action, string $slug, int $numero): string
    {
        return sprintf(
            '%s/%s/%s/%s/%d',
            $this->baseUri,
            self::AJAX_PATH,
            $action,
            rawurlencode($slug),
            $numero
        );
    }

    /**
     * Récupère le manga ou lance une exception.
     */
    private function resolveMangaOrFail(string $action, string $slug, int $numero): object
    {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            throw new NotFoundException('Manga introuvable');
        }

        if ($slug !== $data->canonicalSlug) {
            throw new BaseHttpException(
                message: 'URL non canonique',
                statusCode: 409,
                data: [
                    'redirect' => $this->canonicalRedirect($action, $data->canonicalSlug, $numero)
                ]
            );
        }

        return $data;
    }

    public function seriesPage(string $page = '1'): never
    {
        $this->ensureAjax();

        $data = $this->mangaReadService->series($page);

        if ($data === null) {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderPartial('manga/partials/series_ajax', [
            'mangas' => $data->mangas,
            'compteur' => $data->compteur,
            'currentPage' => $data->currentPage,
            'slugFilter' => $data->slugFilter,
        ]);
    }

    public function search(string $query = ''): never
    {
        $this->ensureAjax();

        $results = $this->mangaReadService->searchResults($query);

        $this->json([
            'success' => true,
            'data' => ['results' => $results]
        ]);
    }

    public function updateNote(MangaUpdateNoteRequest $request, string $slug, int $numero): never
    {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail('update-note', $slug, $numero);

        if ($request->fails()) {
            throw new ValidationException($request->errors());
        }

        $result = $this->mangaWriteService->updateNote($data->canonicalSlug, $numero, $request->dto());

        $this->json($result->toArray(), $result->status);
    }

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

    public function delete(string $slug, int $numero): never
    {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail('delete', $slug, $numero);

        $result = $this->mangaWriteService->delete($data->canonicalSlug, $numero);

        $this->json($result->toArray(), $result->status);
    }
}