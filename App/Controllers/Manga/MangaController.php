<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
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
        $requestedSlug = trim($requestedSlug);
        $canonicalSlug = trim($canonicalSlug);

        if (
            $canonicalSlug === ''
            || $requestedSlug === $canonicalSlug
        ) {
            return;
        }

        $location = sprintf(
            '%s/%s',
            trim($pathPrefix, '/'),
            rawurlencode($canonicalSlug),
        );

        if ($numero !== null) {
            $location .= '/' . $numero;
        }

        $currentPath = trim(
            $this->request->path(),
            '/',
        );

        if (
            trim($location, '/')
            === $currentPath
        ) {
            return;
        }

        $this->redirect(
            $location,
            301,
        );
    }

    private function editPath(
        string $slug,
        int $numero,
    ): string {
        return sprintf(
            '%s/%s/%d',
            self::EDIT_PATH,
            rawurlencode($slug),
            $numero,
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function jsonValidationError(
        array $errors,
    ): never {
        $this->jsonError(
            'Formulaire invalide',
            422,
            [
                'errors' => $errors,
            ],
        );
    }

    private function jsonError(
        string $message,
        int $status,
        array $data = [],
    ): never {
        $this->json(
            ServiceResult::error(
                message: $message,
                data: $data,
                status: $status,
            )->toArray(),
            $status,
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
            $this->title .= sprintf(
                ' - Page %d',
                $data->currentPage,
            );
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

        if (
            isset($data->slugFilter)
            && $data->slugFilter !== ''
        ) {
            $this->redirectToCanonicalUrl(
                $requestedSlug,
                $data->slugFilter,
                self::SERIES_PATH,
            );
        }

        if (!isset($data->mangas[0])) {
            $this->notFound(
                'Manga introuvable',
            );
        }

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
            $this->jsonValidationError(
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
                $this->jsonError(
                    'Manga introuvable',
                    404,
                );
            }

            $this->notFound(
                'Manga introuvable',
            );
        }

        $redirectPath = $this->editPath(
            $data->canonicalSlug,
            $numero,
        );

        if ($slug !== $data->canonicalSlug) {
            if ($isAjax) {
                $this->jsonError(
                    'URL non canonique',
                    409,
                    [
                        'redirect' => $redirectPath,
                    ],
                );
            }

            $this->redirectToCanonicalUrl(
                $slug,
                $data->canonicalSlug,
                self::EDIT_PATH,
                $numero,
            );
        }

        if ($request->fails()) {
            if ($isAjax) {
                $this->jsonValidationError(
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
            sprintf(
                '%s/%s/%d',
                self::SERIES_PATH,
                rawurlencode($data->canonicalSlug),
                $numero,
            ),
            $result->message,
        );
    }
}