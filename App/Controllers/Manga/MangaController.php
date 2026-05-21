<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Http\Request;

final class MangaController extends Controller
{
    private const SERIES_PATH = 'manga/series';

    private const EDIT_PATH = 'manga/series/modifier';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null,
    ): void {
        if ($requestedSlug === $canonicalSlug) {
            return;
        }

        $location = trim($pathPrefix, '/')
            . '/'
            . rawurlencode($canonicalSlug);

        if ($numero !== null) {
            $location .= '/' . $numero;
        }

        $this->redirect(
            $location,
            301,
        );
    }

    /**
     * @param object{
     *     mangas: mixed,
     *     compteur: mixed,
     *     slugFilter: mixed,
     *     currentPage: int
     * } $data
     */
    private function renderSeriesPage(
        object $data,
    ): never {
        $this->render(
            'manga/series',
            [
                'mangas' => $data->mangas,
                'compteur' => $data->compteur,
                'slugFilter' => $data->slugFilter,
                'currentPage' => $data->currentPage,
            ],
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validationErrorResponse(
        array $errors,
    ): never {
        $this->json([
            'success' => false,
            'status' => 422,
            'message' => 'Formulaire invalide',
            'errors' => $errors,
        ], 422);
    }

    public function index(): never
    {
        $this->title = 'Manga';

        $this->render(
            'manga/index',
        );
    }

    public function lien(): never
    {
        $this->title = 'Manga | Lien';

        $this->render(
            'manga/lien',
        );
    }

    public function series(
        string $page = '1',
    ): never {
        $data = $this->mangaReadService
            ->series($page);

        if ($data === null) {
            $this->notFound(
                'Page introuvable',
            );
        }

        $this->title = 'Manga | Series';

        if ($data->currentPage > 1) {
            $this->title .=
                ' - Page '
                . $data->currentPage;
        }

        $this->renderSeriesPage($data);
    }

    public function recherche(
        string $query = '',
    ): never {
        $data = $this->mangaReadService
            ->search($query);

        $this->title = $data->search !== ''
            ? 'Manga | Recherche : '
                . $data->search
            : 'Manga | Recherche';

        $this->render(
            'manga/search',
            [
                'mangas' => $data->mangas,
                'search' => $data->search,
            ],
        );
    }

    public function serie(
        string $slug,
    ): never {
        $requestedSlug = trim($slug);

        $data = $this->mangaReadService
            ->serie($requestedSlug);

        if ($data === null) {
            $this->notFound(
                'Manga introuvable',
            );
        }

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $data->slugFilter ?? '',
            self::SERIES_PATH,
        );

        $this->title =
            'Manga | '
            . $data->mangas[0]->livre;

        $this->renderSeriesPage($data);
    }

    public function show(
        string $slug,
        int $numero,
    ): never {
        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->notFound(
                'Manga introuvable',
            );
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data->canonicalSlug,
            self::SERIES_PATH,
            $numero,
        );

        $this->title =
            'Manga | '
            . $data->manga->livre;

        $this->render(
            'manga/livre',
            [
                'manga' => $data->manga,
            ],
        );
    }

    public function ajouter(): never
    {
        $this->title = 'Manga | Ajouter';

        $this->render(
            'manga/ajouter',
        );
    }

    public function modifier(
        string $slug,
        int $numero,
    ): never {
        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->notFound(
                'Manga introuvable',
            );
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data->canonicalSlug,
            self::EDIT_PATH,
            $numero,
        );

        $this->title = 'Manga | Modifier';

        $this->render(
            'manga/modifier',
            [
                'manga' => $data->manga,
            ],
        );
    }

    public function ajouterTraitement(
        MangaCreateRequest $request,
    ): never {
        if ($request->fails()) {
            $this->validationErrorResponse(
                $request->errors(),
            );
        }

        $result = $this->mangaWriteService
            ->create(
                $request->dto(),
                $request->files(),
            );

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }

    public function update(
        MangaUpdateRequest $request,
        string $slug,
        int $numero,
    ): never {
        $isAjax = $this->isAjax();

        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Manga introuvable',
                ], 404);
            }

            $this->notFound(
                'Manga introuvable',
            );
        }

        if ($slug !== $data->canonicalSlug) {
            $redirect = $this->basePath
                . self::EDIT_PATH
                . '/'
                . rawurlencode(
                    $data->canonicalSlug,
                )
                . '/'
                . $numero;

            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'URL non canonique',
                    'redirect' => $redirect,
                ], 409);
            }

            $this->redirectToCanonicalUrl(
                $slug,
                $data->canonicalSlug,
                self::EDIT_PATH,
                $numero,
            );
        }

        $redirectPath = self::EDIT_PATH
            . '/'
            . rawurlencode(
                $data->canonicalSlug,
            )
            . '/'
            . $numero;

        if ($request->fails()) {
            if ($isAjax) {
                $this->validationErrorResponse(
                    $request->errors(),
                );
            }

            $this->redirectWithValidationErrors(
                $redirectPath,
                $request->errors(),
            );
        }

        $result = $this->mangaWriteService
            ->update(
                $data->canonicalSlug,
                $numero,
                $request->dto(),
                $request->files(),
            );

        if ($isAjax) {
            $this->json(
                $result->toArray(),
                $result->status,
            );
        }

        if (!$result->success) {
            $this->redirectWithError(
                $redirectPath,
                $result->message,
            );
        }

        $this->redirectWithSuccess(
            self::SERIES_PATH
            . '/'
            . rawurlencode(
                $data->canonicalSlug,
            )
            . '/'
            . $numero,
            $result->message,
        );
    }
}