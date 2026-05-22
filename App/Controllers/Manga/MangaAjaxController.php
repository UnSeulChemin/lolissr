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

/**
 * Controller pour toutes les actions AJAX liées aux mangas.
 */
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
     * Vérifie que la requête est AJAX ou en test.
     *
     * @throws BaseHttpException
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
     * Génère l'URL de redirection canonique pour les opérations AJAX.
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
     * Récupère un manga ou lève une exception si non trouvé ou URL non canonique.
     *
     * @throws NotFoundException
     * @throws BaseHttpException
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

    /**
     * Retourne une page de séries en HTML partiel (AJAX).
     *
     * @throws NotFoundException
     */
    public function seriesPage(string $page = '1'): void
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

    /**
     * Retourne les résultats de recherche au format JSON.
     */
    public function search(string $query = ''): never
    {
        $this->ensureAjax();

        $results = $this->mangaReadService->searchResults($query);

        $this->json([
            'success' => true,
            'data' => ['results' => $results]
        ]);
    }

    /**
     * Met à jour la note d'un manga via AJAX.
     *
     * @throws ValidationException
     */
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

    /**
     * Met à jour le statut "lu" d'un manga via AJAX.
     */
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

    /**
     * Supprime un manga via AJAX.
     */
    public function delete(string $slug, int $numero): never
    {
        $this->ensureAjax();
        $data = $this->resolveMangaOrFail('delete', $slug, $numero);

        $result = $this->mangaWriteService->delete($data->canonicalSlug, $numero);
        $this->json($result->toArray(), $result->status);
    }
}