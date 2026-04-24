<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Core\Support\Session;
use App\Core\Support\Str;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Repositories\Manga\MangaRepository;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

final class MangaController extends Controller
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
        int $numero
    ): object {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data !== null) {
            return $data['manga'];
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
        $request = new MangaCreateRequest();

        if ($request->fails()) {
            $this->redirectWithValidationErrors(
                'manga/ajouter',
                $request->errors()
            );
        }

        $result = $this->mangaWriteService->create(
            $request->data(),
            $request->files()
        );

        if (!$result['success']) {
            $this->redirectWithError(
                'manga/ajouter',
                $result['message'] ?? 'Erreur'
            );
        }

        Session::forget(['errors', 'old']);

        $this->redirectWithSuccess(
            'manga/ajouter',
            $result['message']
        );
    }

    public function update(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        $request = new MangaUpdateRequest();

        $redirectPath = 'manga/modifier/' . rawurlencode($slug) . '/' . $numero;

        if ($request->fails()) {
            $this->redirectWithValidationErrors(
                $redirectPath,
                $request->errors()
            );
        }

        $result = $this->mangaWriteService->update(
            $slug,
            $numero,
            $request->data(),
            $request->files()
        );

        if (!$result['success']) {
            $this->redirectWithError(
                $redirectPath,
                $result['message'] ?? 'Erreur'
            );
        }

        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            $result['message']
        );
    }
}