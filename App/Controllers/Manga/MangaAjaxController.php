<?php
declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;

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
                'data' => ['results' => $results],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // -----------------------------
    // AJAX Series Page
    // -----------------------------
    public function seriesPage(string|int $page = 1): void
    {
        $this->ensureAjax();
        $page = max(1, (int)$page);
        $data = $this->mangaReadService->series($page);

        if ($data === null) throw new NotFoundException('Page introuvable');

        $this->renderPartial('manga/partials/series_page_ajax', [
            'mangas' => $data->mangas,
            'currentPage' => $data->currentPage,
            'totalPages' => $data->compteur,
            'slugFilter' => $data->slugFilter,
            'isSerieView' => $data->slugFilter !== null,
            'baseUri' => $this->baseUri,
        ]);
    }

    // -----------------------------
    // Update note
    // -----------------------------
    public function updateNote(string $slug, int $numero): void
    {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail('update-note', $slug, $numero);

        // Récupération des champs POST
        $jacquette = (int)$this->request->input('jacquette', 0);
        $livreNote = (int)$this->request->input('livre_note', 0);

        // Validation simple
        if ($jacquette < 1 || $jacquette > 5 || $livreNote < 1 || $livreNote > 5) {
            $this->json(['success' => false, 'message' => 'Valeurs invalides'], 400);
            return;
        }

        // Calcul note totale
        $noteTotale = $jacquette + $livreNote;

        // Création du DTO
        $dto = MangaUpdateNoteDTO::fromArray([
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
        ]);

        // Appel du service
        $result = $this->mangaWriteService->updateNote(
            $data->canonicalSlug,
            $numero,
            $dto,
            $noteTotale
        );

        // Préparer la réponse pour le front-end
        $response = $result->toArray();
        $response['data']['notes'] = [
            'jacquette' => $jacquette,
            'livreNote' => $livreNote,
            'note' => $noteTotale
        ];

        $this->json($response, $result->status);
    }

    // -----------------------------
    // Update read status
    // -----------------------------
    public function updateReadStatus(string $slug, int $numero): void
    {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail('update-read-status', $slug, $numero);
        $readStatus = (int)$this->request->input('readStatus', 0);

        $result = $this->mangaWriteService->updateReadStatus(
            $data->canonicalSlug,
            $numero,
            $readStatus
        );

        $this->json($result->toArray(), $result->status);
    }

    // -----------------------------
    // Delete manga
    // -----------------------------
    public function delete(string $slug, int $numero): void
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