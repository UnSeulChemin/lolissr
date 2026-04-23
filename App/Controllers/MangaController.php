<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Support\Session;
use App\Core\Support\Str;
use App\Repositories\MangaRepository;
use App\Services\MangaReadService;
use App\Services\MangaService;

class MangaController extends Controller
{
    protected MangaRepository $mangaRepository;
    protected MangaService $mangaService;
    protected MangaReadService $mangaReadService;

    public function __construct()
    {
        parent::__construct();

        $this->mangaRepository = new MangaRepository();
        $this->mangaService = new MangaService($this->mangaRepository);
        $this->mangaReadService = new MangaReadService($this->mangaRepository);
    }

    protected function mangaRepository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    protected function mangaService(): MangaService
    {
        return $this->mangaService;
    }

    protected function mangaReadService(): MangaReadService
    {
        return $this->mangaReadService;
    }

    /**
     * Retourne une réponse JSON.
     *
     * @param array<string, mixed> $data
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        json($data, $statusCode);
    }

    /**
     * Retourne un payload JSON d’erreur standard.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    protected function jsonErrorPayload(array $result): array
    {
        $payload = [
            'success' => false,
            'message' => (string) ($result['message'] ?? 'Une erreur est survenue'),
        ];

        if (isset($result['errors']))
        {
            $payload['errors'] = $result['errors'];
        }

        if (isset($result['redirect']))
        {
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
        if ($requestedSlug === $canonicalSlug)
        {
            return;
        }

        $location = trim($pathPrefix, '/') . '/' . rawurlencode($canonicalSlug);

        if ($numero !== null)
        {
            $location .= '/' . $numero;
        }

        $this->redirect($location, 301);
    }

    protected function findCanonicalMangaOrFail(
        string $slug,
        int $numero,
        bool $ajax = false
    ): object {
        $data = $this->mangaReadService()->one($slug, $numero);

        if ($data !== null)
        {
            return $data['manga'];
        }

        if ($ajax)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Manga introuvable',
            ], 404);
        }

        $this->notFound('Manga introuvable');
    }

    protected function handleCanonicalUpdateAccess(
        string $requestedSlug,
        object $manga,
        int $numero,
        bool $ajax = false
    ): string {
        $canonicalSlug = Str::slug((string) $manga->slug);
        $redirect = $this->basePath
            . 'manga/modifier/'
            . rawurlencode($canonicalSlug)
            . '/'
            . $numero;

        if ($requestedSlug === $canonicalSlug)
        {
            return $canonicalSlug;
        }

        if ($ajax)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => $redirect,
            ], 409);
        }

        $this->redirect(
            'manga/modifier/' . rawurlencode($canonicalSlug) . '/' . $numero,
            301
        );
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function handleUpdateResult(
        array $result,
        string $slug,
        int $numero,
        bool $ajax = false
    ): void {
        if (!$result['success'])
        {
            if ($ajax)
            {
                $this->jsonResponse(
                    $this->jsonErrorPayload($result),
                    (int) $result['status']
                );
            }

            if ((int) $result['status'] === 422)
            {
                $this->redirectWithValidationErrors(
                    'manga/modifier/' . rawurlencode($slug) . '/' . $numero,
                    $result['errors'] ?? []
                );
            }

            $this->redirectWithError(
                'manga/modifier/' . rawurlencode($slug) . '/' . $numero,
                (string) $result['message']
            );
        }

        Session::forget(['errors', 'old']);

        if ($ajax)
        {
            $fresh = $this->mangaReadService()->one($slug, $numero);

            if ($fresh === null)
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Manga introuvable',
                ], 404);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notes mises à jour',
                'jacquette' => $fresh['manga']->jacquette,
                'livre_note' => $fresh['manga']->livre_note,
                'note' => $fresh['manga']->note,
            ]);
        }

        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            (string) $result['message']
        );
    }

    protected function performUpdate(
        string $slug,
        string $numero,
        bool $ajax = false
    ): void {
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        if (!Request::isPost())
        {
            if ($ajax)
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée',
                ], 405);
            }

            $this->methodNotAllowed(
                'Méthode non autorisée pour la modification d’un manga'
            );
        }

        $manga = $this->findCanonicalMangaOrFail($requestedSlug, $numero, $ajax);

        $canonicalSlug = $this->handleCanonicalUpdateAccess(
            $requestedSlug,
            $manga,
            $numero,
            $ajax
        );

        $result = $this->mangaService()->update(
            $canonicalSlug,
            $numero,
            Request::allPost(),
            Request::allFiles()
        );

        $this->handleUpdateResult($result, $canonicalSlug, $numero, $ajax);
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
        $data = $this->mangaReadService()->collection($page);

        if ($data === null)
        {
            $this->notFound('Page introuvable');
        }

        $this->title = 'Manga | Collection';

        if ($data['currentPage'] > 1)
        {
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
        $data = $this->mangaReadService()->collection($page);

        if ($data === null)
        {
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
        $data = $this->mangaReadService()->search($query);

        $this->title = 'Manga | Recherche';

        if ($data['search'] !== '')
        {
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
            $this->mangaReadService()->searchAjax($query)
        );
    }

    public function serie(string $slug): void
    {
        $requestedSlug = trim($slug);
        $data = $this->mangaReadService()->serie($slug);

        if ($data === null)
        {
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
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        $data = $this->mangaReadService()->one($slug, $numero);

        if ($data === null)
        {
            $this->notFound('Manga introuvable');
        }

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $data['canonicalSlug'],
            'manga',
            (int) $data['manga']->numero
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
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        $manga = $this->findCanonicalMangaOrFail($requestedSlug, $numero);
        $canonicalSlug = Str::slug((string) $manga->slug);

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $canonicalSlug,
            'manga/modifier',
            (int) $manga->numero
        );

        $this->title = 'Manga | Modifier';

        $this->render('manga/modifier', [
            'manga' => $manga,
        ]);
    }

    public function ajouterTraitement(): void
    {
        if (!Request::isPost())
        {
            if (is_ajax())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée',
                ], 405);
            }

            $this->methodNotAllowed(
                'Méthode non autorisée pour l’ajout d’un manga'
            );
        }

        $result = $this->mangaService()->create(
            Request::allPost(),
            Request::allFiles()
        );

        if (!$result['success'])
        {
            if (is_ajax())
            {
                $this->jsonResponse(
                    $this->jsonErrorPayload($result),
                    (int) $result['status']
                );
            }

            if ((int) $result['status'] === 422)
            {
                $this->redirectWithValidationErrors(
                    'manga/ajouter',
                    $result['errors'] ?? []
                );
            }

            $this->redirectWithError(
                'manga/ajouter',
                (string) $result['message']
            );
        }

        Session::forget(['errors', 'old']);

        if (is_ajax())
        {
            $payload = [
                'success' => true,
                'message' => (string) $result['message'],
            ];

            if (isset($result['file']))
            {
                $payload['file'] = $result['file'];
            }

            $this->jsonResponse($payload);
        }

        $this->redirectWithSuccess(
            'manga/ajouter',
            (string) $result['message']
        );
    }

    public function update(string $slug, string $numero): void
    {
        $this->performUpdate($slug, $numero, is_ajax());
    }

    public function ajaxUpdateNote(string $slug, string $numero): void
    {
        $this->performUpdate($slug, $numero, true);
    }

    public function ajaxDelete(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        if (!Request::isPost())
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Méthode non autorisée',
            ], 405);
        }

        if (!is_ajax())
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Requête AJAX requise',
            ], 400);
        }

        $requestedSlug = trim($slug);
        $manga = $this->findCanonicalMangaOrFail($requestedSlug, $numero, true);
        $canonicalSlug = Str::slug((string) $manga->slug);

        if ($requestedSlug !== $canonicalSlug)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => $this->basePath
                    . 'manga/'
                    . rawurlencode($canonicalSlug)
                    . '/'
                    . $numero,
            ], 409);
        }

        $result = $this->mangaService()->delete($canonicalSlug, $numero);

        if (!$result['success'])
        {
            $this->jsonResponse(
                $this->jsonErrorPayload($result),
                (int) $result['status']
            );
        }

        $this->jsonResponse([
            'success' => true,
            'message' => (string) $result['message'],
            'redirect' => $this->basePath
                . 'manga/serie/'
                . rawurlencode($canonicalSlug),
        ], 200);
    }
}