<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Support\Session;
use App\Core\Support\Str;
use App\Repositories\MangaRepository;
use App\Services\MangaReadService;
use App\Services\MangaService;

final class MangaController extends Controller
{
    protected MangaRepository $mangaRepository;
    protected MangaService $mangaService;
    protected MangaReadService $mangaReadService;

    public function __construct()
    {
        parent::__construct();

        $this->mangaRepository = app(MangaRepository::class);
        $this->mangaService = app(MangaService::class);
        $this->mangaReadService = app(MangaReadService::class);
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        json($data, $statusCode);
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

    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null
    ): void {
        $requestedSlug = Str::slug($requestedSlug);

        if ($requestedSlug === $canonicalSlug) {
            return;
        }

        $location = trim($pathPrefix, '/') . '/' . rawurlencode($canonicalSlug);

        if ($numero !== null) {
            $location .= '/' . $numero;
        }

        $this->redirect($location, 301);
    }

    protected function findCanonicalMangaOrFail(
        string $slug,
        int $numero,
        bool $ajax = false
    ): object {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data !== null) {
            return $data['manga'];
        }

        if ($ajax) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Manga introuvable',
            ], 404);
        }

        $this->notFound('Manga introuvable');
    }

    public function index(): void
    {
        $this->title = 'Manga';

        $this->render('manga/index');
    }

    public function lien(): void
    {
        $this->title = 'Manga | Lien';

        $this->render('manga/lien');
    }

    public function collection(string $page = '1'): void
    {
        $data = $this->mangaReadService->collection($page);

        if ($data === null) {
            $this->notFound('Page introuvable');
        }

        $this->title = 'Manga | Collection';

        if ($data['currentPage'] > 1) {
            $this->title .= ' - Page ' . $data['currentPage'];
        }

        $this->render('manga/collection', [
            'mangas' => $data['mangas'],
            'compteur' => $data['compteur'],
            'slugFilter' => null,
            'currentPage' => $data['currentPage'],
        ]);
    }

    public function collectionAjax(string $page = '1'): void
    {
        $data = $this->mangaReadService->collection($page);

        if ($data === null) {
            $this->notFound('Page introuvable');
        }

        $this->renderPartial('manga/partials/collection_ajax', [
            'mangas' => $data['mangas'],
            'compteur' => $data['compteur'],
            'currentPage' => $data['currentPage'],
            'slugFilter' => null,
        ]);
    }

    public function recherche(string $query = ''): void
    {
        $data = $this->mangaReadService->search($query);

        $this->title = 'Manga | Recherche';

        if ($data['search'] !== '') {
            $this->title = 'Manga | Recherche : ' . $data['search'];
        }

        $this->render('manga/search', [
            'mangas' => $data['mangas'],
            'search' => $data['search'],
        ]);
    }

    public function searchAjax(string $query = ''): void
    {
        $this->jsonResponse(
            $this->mangaReadService->searchAjax($query)
        );
    }

    public function serie(string $slug): void
    {
        $requestedSlug = trim($slug);

        $data = $this->mangaReadService->serie($requestedSlug);

        if ($data === null) {
            $this->notFound('Manga introuvable');
        }

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $data['canonicalSlug'],
            'manga/serie'
        );

        $this->title = 'Manga | ' . $data['mangas'][0]->livre;

        $this->render('manga/collection', [
            'mangas' => $data['mangas'],
            'compteur' => null,
            'slugFilter' => $data['canonicalSlug'],
            'currentPage' => 1,
        ]);
    }

    public function show(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            $this->notFound('Manga introuvable');
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data['canonicalSlug'],
            'manga',
            $numero
        );

        $this->title = 'Manga | ' . $data['manga']->livre;

        $this->render('manga/livre', [
            'manga' => $data['manga'],
        ]);
    }

    public function ajouter(): void
    {
        $this->title = 'Manga | Ajouter';

        $this->render('manga/ajouter');
    }

    public function modifier(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        $manga = $this->findCanonicalMangaOrFail($slug, $numero);

        $canonicalSlug = Str::slug((string) $manga->slug);

        $this->redirectToCanonicalUrl(
            $slug,
            $canonicalSlug,
            'manga/modifier',
            $numero
        );

        $this->title = 'Manga | Modifier';

        $this->render('manga/modifier', [
            'manga' => $manga,
        ]);
    }

    public function ajouterTraitement(): void
    {
        $isAjax = is_ajax();

        $result = $this->mangaService->create(
            Request::allPost(),
            Request::allFiles()
        );

        if (!$result['success']) {
            if ($isAjax) {
                $this->jsonResponse(
                    $this->jsonErrorPayload($result),
                    (int) ($result['status'] ?? 500)
                );
            }

            if (($result['status'] ?? 500) === 422) {
                $this->redirectWithValidationErrors(
                    'manga/ajouter',
                    $result['errors'] ?? []
                );
            }

            $this->redirectWithError(
                'manga/ajouter',
                $result['message'] ?? 'Erreur'
            );
        }

        Session::forget(['errors', 'old']);

        if ($isAjax) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        $this->redirectWithSuccess(
            'manga/ajouter',
            $result['message']
        );
    }

    public function update(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        $result = $this->mangaService->update(
            $slug,
            $numero,
            Request::allPost(),
            Request::allFiles()
        );

        $this->handleUpdateResult(
            $result,
            $slug,
            $numero,
            is_ajax()
        );
    }

    private function handleUpdateResult(
        array $result,
        string $slug,
        int $numero,
        bool $isAjax
    ): void {
        if (!$result['success']) {
            if ($isAjax) {
                $this->jsonResponse(
                    $this->jsonErrorPayload($result),
                    (int) ($result['status'] ?? 500)
                );
            }

            if (($result['status'] ?? 500) === 422) {
                $this->redirectWithValidationErrors(
                    'manga/modifier/' . rawurlencode($slug) . '/' . $numero,
                    $result['errors'] ?? []
                );
            }

            $this->redirectWithError(
                'manga/modifier/' . rawurlencode($slug) . '/' . $numero,
                $result['message'] ?? 'Erreur'
            );
        }

        if ($isAjax) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            $result['message']
        );
    }

    public function ajaxUpdateNote(string $slug, string $numero): void
    {
        if (!is_ajax()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $numero = (int) $numero;

        $result = $this->mangaService->updateNote(
            $slug,
            $numero,
            Request::allPost()
        );

        $this->jsonResponse(
            $result,
            (int) ($result['status'] ?? 200)
        );
    }

    public function ajaxDelete(string $slug, string $numero): void
    {
        if (!is_ajax()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $numero = (int) $numero;

        $result = $this->mangaService->delete($slug, $numero);

        if (!$result['success']) {
            $this->jsonResponse(
                $this->jsonErrorPayload($result),
                (int) ($result['status'] ?? 500)
            );
        }

        $redirectSlug = (string) ($result['canonicalSlug'] ?? $slug);

        $this->jsonResponse([
            'success' => true,
            'message' => $result['message'],
            'redirect' => $this->basePath
                . 'manga/serie/'
                . rawurlencode($redirectSlug),
        ]);
    }
}