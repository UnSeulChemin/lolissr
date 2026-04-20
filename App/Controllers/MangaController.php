<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
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

    /**
     * Retourne le repository manga.
     */
    protected function mangaRepository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    /**
     * Retourne le service manga.
     */
    protected function mangaService(): MangaService
    {
        return $this->mangaService;
    }

    /**
     * Retourne le service de lecture manga.
     */
    protected function mangaReadService(): MangaReadService
    {
        return $this->mangaReadService;
    }

    /**
     * Vérifie si la requête est AJAX.
     */
    protected function isAjaxRequest(): bool
    {
        $requestedWith = Request::server('HTTP_X_REQUESTED_WITH', '');

        return strtolower(trim((string) $requestedWith)) === 'xmlhttprequest';
    }

    /**
     * Retourne une réponse JSON.
     *
     * @param array<string, mixed> $data
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
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
            'message' => (string) ($result['message'] ?? 'Une erreur est survenue')
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

    /**
     * Redirige vers l'URL canonique si le slug demandé n'est pas correct.
     */
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

        Response::redirect($this->basePath . $location, 301);
    }

    /**
     * Retourne un manga ou déclenche une 404 / JSON 404.
     */
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
                'message' => 'Manga introuvable'
            ], 404);
        }

        $this->notFound('Manga introuvable');
    }

    /**
     * Gère l’accès canonique pour une route modifier.
     */
    protected function handleCanonicalUpdateAccess(
        string $requestedSlug,
        object $manga,
        int $numero,
        bool $ajax = false
    ): string {
        $canonicalSlug = Str::slug((string) $manga->slug);
        $redirect = $this->basePath . 'manga/modifier/' . rawurlencode($canonicalSlug) . '/' . $numero;

        if ($requestedSlug === $canonicalSlug)
        {
            return $canonicalSlug;
        }

        if ($ajax)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => $redirect
            ], 409);
        }

        Response::redirect($redirect, 301);
    }

    /**
     * Gère la réponse après update.
     *
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
                    'message' => 'Manga introuvable'
                ], 404);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notes mises à jour',
                'jacquette' => $fresh['manga']->jacquette,
                'livre_note' => $fresh['manga']->livre_note,
                'note' => $fresh['manga']->note
            ]);
        }

        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            (string) $result['message']
        );
    }

    /**
     * Logique partagée de mise à jour.
     */
    protected function performUpdate(string $slug, string $numero, bool $ajax = false): void
    {
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        if (!Request::isPost())
        {
            if ($ajax)
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée'
                ], 405);
            }

            $this->methodNotAllowed('Méthode non autorisée pour la modification d’un manga');
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

    /**
     * Accueil manga.
     * Route : /manga
     */
    public function index(): void
    {
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * Page lien.
     * Route : /manga/lien
     */
    public function lien(): void
    {
        $this->title = 'Manga | Lien';
        $this->render('manga/lien');
    }

    /**
     * Collection paginée.
     * Routes : /manga/collection | /manga/collection/page/{page}
     */
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
            'currentPage' => $data['currentPage']
        ]);
    }

    /**
     * Collection AJAX.
     * Route : /manga/collection-ajax/page/{page}
     */
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
            'slugFilter' => null
        ]);
    }

    /**
     * Recherche manga.
     * Route : /manga/recherche/{query}
     */
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
            'search' => $data['search']
        ]);
    }

    /**
     * Recherche AJAX (live search).
     * Route : /manga/search-ajax/{query}
     */
    public function searchAjax(string $query = ''): void
    {
        $this->jsonResponse(
            $this->mangaReadService()->searchAjax($query)
        );
    }

    /**
     * Affiche une série.
     * Route : /manga/serie/{slug}
     */
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
            'manga/serie/'
        );

        $this->title = 'Manga | ' . $data['mangas'][0]->livre;

        $this->render('manga/collection', [
            'mangas' => $data['mangas'],
            'compteur' => null,
            'slugFilter' => $data['canonicalSlug'],
            'currentPage' => 1
        ]);
    }

    /**
     * Affiche un tome.
     * Route : /manga/{slug}/{numero}
     */
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
            'manga/',
            (int) $data['manga']->numero
        );

        $this->title = 'Manga | ' . $data['manga']->livre;

        $this->render('manga/livre', [
            'manga' => $data['manga']
        ]);
    }

    /**
     * Formulaire d'ajout.
     * Route : /manga/ajouter
     */
    public function ajouter(): void
    {
        $this->title = 'Manga | Ajouter';
        $this->render('manga/ajouter');
    }

    /**
     * Formulaire de modification.
     * Route : /manga/modifier/{slug}/{numero}
     */
    public function modifier(string $slug, string $numero): void
    {
        $requestedSlug = trim($slug);
        $numero = (int) $numero;

        $manga = $this->findCanonicalMangaOrFail($requestedSlug, $numero);
        $canonicalSlug = Str::slug((string) $manga->slug);

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $canonicalSlug,
            'manga/modifier/',
            (int) $manga->numero
        );

        $this->title = 'Manga | Modifier';

        $this->render('manga/modifier', [
            'manga' => $manga
        ]);
    }

    /**
     * Traite l'ajout.
     * Route : POST /manga/ajouter
     */
    public function ajouterTraitement(): void
    {
        if (!Request::isPost())
        {
            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée'
                ], 405);
            }

            $this->methodNotAllowed('Méthode non autorisée pour l’ajout d’un manga');
        }

        $result = $this->mangaService()->create(
            Request::allPost(),
            Request::allFiles()
        );

        if (!$result['success'])
        {
            if ($this->isAjaxRequest())
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

            $this->redirectWithError('manga/ajouter', (string) $result['message']);
        }

        Session::forget(['errors', 'old']);

        if ($this->isAjaxRequest())
        {
            $payload = [
                'success' => true,
                'message' => (string) $result['message']
            ];

            if (isset($result['file']))
            {
                $payload['file'] = $result['file'];
            }

            $this->jsonResponse($payload);
        }

        $this->redirectWithSuccess('manga/ajouter', (string) $result['message']);
    }

    /**
     * Traite la modification.
     * Route : POST /manga/modifier/{slug}/{numero}
     */
    public function update(string $slug, string $numero): void
    {
        $this->performUpdate($slug, $numero, $this->isAjaxRequest());
    }

    /**
     * Mise à jour AJAX des notes.
     * Route : POST /manga/ajax/update-note/{slug}/{numero}
     */
    public function ajaxUpdateNote(string $slug, string $numero): void
    {
        $this->performUpdate($slug, $numero, true);
    }
}