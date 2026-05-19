<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

final class MangaController extends Controller
{
    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService
    ) {
        parent::__construct();
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

        $location = trim($pathPrefix, '/')
            . '/'
            . rawurlencode($canonicalSlug);

        if ($numero !== null)
        {
            $location .= '/' . $numero;
        }

        $this->redirect($location, 301);
    }

    public function index(): never
    {
        $this->title = 'Manga';

        $this->render('manga/index');
    }

    public function lien(): never
    {
        $this->title = 'Manga | Lien';

        $this->render('manga/lien');
    }

    public function series(
        string $page = '1'
    ): never {
        $data = $this->mangaReadService
            ->series($page);

        if ($data === null)
        {
            $this->notFound(
                'Page introuvable'
            );
        }

        $this->title = 'Manga | Series';

        if ($data->currentPage > 1)
        {
            $this->title .=
                ' - Page '
                . $data->currentPage;
        }

        $this->render('manga/series', [
            'mangas' => $data->mangas,
            'compteur' => $data->compteur,
            'slugFilter' => $data->slugFilter,
            'currentPage' => $data->currentPage,
        ]);
    }

    public function recherche(
        string $query = ''
    ): never {
        $data = $this->mangaReadService
            ->search($query);

        $this->title = $data->search !== ''
            ? 'Manga | Recherche : '
                . $data->search
            : 'Manga | Recherche';

        $this->render('manga/search', [
            'mangas' => $data->mangas,
            'search' => $data->search,
        ]);
    }

    public function serie(
        string $slug
    ): never {
        $requestedSlug = trim($slug);

        $data = $this->mangaReadService
            ->serie($requestedSlug);

        if ($data === null)
        {
            $this->notFound(
                'Manga introuvable'
            );
        }

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $data->slugFilter ?? '',
            'manga/series'
        );

        $this->title =
            'Manga | '
            . $data->mangas[0]->livre;

        $this->render('manga/series', [
            'mangas' => $data->mangas,
            'compteur' => $data->compteur,
            'slugFilter' => $data->slugFilter,
            'currentPage' => $data->currentPage,
        ]);
    }

    public function show(
        string $slug,
        int $numero
    ): never {
        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero
            );

        if ($data === null)
        {
            $this->notFound(
                'Manga introuvable'
            );
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data->canonicalSlug,
            'manga/series',
            $numero
        );

        $this->title =
            'Manga | '
            . $data->manga->livre;

        $this->render('manga/livre', [
            'manga' => $data->manga,
        ]);
    }

    public function ajouter(): never
    {
        $this->title = 'Manga | Ajouter';

        $this->render('manga/ajouter');
    }

    public function modifier(
        string $slug,
        int $numero
    ): never {
        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero
            );

        if ($data === null)
        {
            $this->notFound(
                'Manga introuvable'
            );
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data->canonicalSlug,
            'manga/series/modifier',
            $numero
        );

        $this->title = 'Manga | Modifier';

        $this->render('manga/modifier', [
            'manga' => $data->manga,
        ]);
    }

    public function ajouterTraitement(
        MangaCreateRequest $request
    ): never {
        if ($request->fails())
        {
            json([
                'success' => false,
                'status' => 422,
                'message' => 'Formulaire invalide',
                'errors' => $request->errors(),
            ], 422);
        }

        $result = $this->mangaWriteService
            ->create(
                $request->dto(),
                $request->files()
            );

        json(
            $result->toArray(),
            $result->status
        );
    }

    public function update(
        MangaUpdateRequest $request,
        string $slug,
        int $numero
    ): never {
        $isAjax = is_ajax();

        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero
            );

        if ($data === null)
        {
            if ($isAjax)
            {
                json([
                    'success' => false,
                    'message' => 'Manga introuvable',
                ], 404);
            }

            $this->notFound(
                'Manga introuvable'
            );
        }

        if ($slug !== $data->canonicalSlug)
        {
            $redirect = $this->basePath
                . 'manga/series/modifier/'
                . rawurlencode(
                    $data->canonicalSlug
                )
                . '/'
                . $numero;

            if ($isAjax)
            {
                json([
                    'success' => false,
                    'message' => 'URL non canonique',
                    'redirect' => $redirect,
                ], 409);
            }

            $this->redirectToCanonicalUrl(
                $slug,
                $data->canonicalSlug,
                'manga/series/modifier',
                $numero
            );
        }

        $redirectPath =
            'manga/series/modifier/'
            . rawurlencode(
                $data->canonicalSlug
            )
            . '/'
            . $numero;

        if ($request->fails())
        {
            if ($isAjax)
            {
                json([
                    'success' => false,
                    'message' => 'Formulaire invalide',
                    'errors' => $request->errors(),
                ], 422);
            }

            $this->redirectWithValidationErrors(
                $redirectPath,
                $request->errors()
            );
        }

        $result = $this->mangaWriteService
            ->update(
                $data->canonicalSlug,
                $numero,
                $request->dto(),
                $request->files()
            );

        if ($isAjax)
        {
            json(
                $result->toArray(),
                $result->status
            );
        }

        if (!$result->success)
        {
            $this->redirectWithError(
                $redirectPath,
                $result->message
            );
        }

        $this->redirectWithSuccess(
            'manga/series/'
            . rawurlencode(
                $data->canonicalSlug
            )
            . '/'
            . $numero,
            $result->message
        );
    }
}