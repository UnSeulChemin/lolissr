<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class MangaController extends Controller
{
    private const SERIES_PATH =
        'manga/series';

    private const EDIT_PATH =
        'manga/series/modifier';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {

        parent::__construct(
            $request,
        );
    }

    /*
    |--------------------------------------------------------------
    | Canonical redirect
    |--------------------------------------------------------------
    */

    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null,
    ): void {

        $requestedSlug =
            trim($requestedSlug);

        $canonicalSlug =
            trim($canonicalSlug);

        if (
            $canonicalSlug === ''
            || $requestedSlug === $canonicalSlug
        ) {
            return;
        }

        $location =
            sprintf(
                '%s/%s',
                trim($pathPrefix, '/'),
                rawurlencode(
                    $canonicalSlug,
                ),
            );

        if ($numero !== null) {

            $location .=
                '/' . $numero;
        }

        if (
            trim($location, '/')
            === trim(
                $this->request->path(),
                '/',
            )
        ) {
            return;
        }

        $this->redirect(
            $location,
            301,
        );
    }

    /*
    |--------------------------------------------------------------
    | Build edit path
    |--------------------------------------------------------------
    */

    private function buildEditPath(
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

    /*
    |--------------------------------------------------------------
    | Index
    |--------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title =
            'Manga';

        $this->render(
            'manga/index',
        );
    }

    /*
    |--------------------------------------------------------------
    | Links
    |--------------------------------------------------------------
    */

    public function links(): never
    {
        $this->title =
            'Manga | Lien';

        $this->render(
            'manga/lien',
        );
    }

    /*
    |--------------------------------------------------------------
    | Series list
    |--------------------------------------------------------------
    */

    public function series(
        int|string $page = 1,
    ): never {

        $data =
            $this->mangaReadService
                ->series($page);

        if ($data === null) {

            throw new NotFoundException(
                'Page introuvable',
            );
        }

        $this->title =
            'Manga | Series';

        if (
            $data->currentPage > 1
        ) {

            $this->title .=
                ' - Page '
                . $data->currentPage;
        }

        $this->render(
            'manga/series',
            [
                'mangas' =>
                    $data->mangas,

                'currentPage' =>
                    $data->currentPage,

                'compteur' =>
                    $data->compteur,

                'totalSeries' =>
                    $data->totalSeries,

                'perPage' =>
                    $data->perPage,

                'slugFilter' =>
                    $data->slugFilter,
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Search
    |--------------------------------------------------------------
    */

    public function search(
        string $query = '',
    ): never {

        $data =
            $this->mangaReadService
                ->search($query);

        $this->title =
            $data->search !== ''
                ? 'Manga | Recherche : '
                    . $data->search
                : 'Manga | Recherche';

        $this->render(
            'manga/search',
            [
                'mangas' =>
                    $data->mangas,

                'search' =>
                    $data->search,
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Show series
    |--------------------------------------------------------------
    */

    public function showSeries(
        string $slug,
    ): never {

        $data =
            $this->mangaReadService
                ->showSeries($slug);

        if (
            $data === null
            || empty($data->mangas)
        ) {

            throw new NotFoundException(
                'Manga introuvable',
            );
        }

        $this->title =
            'Manga | '
            . $data->mangas[0]->livre;

        $this->render(
            'manga/series',
            [
                'mangas' =>
                    $data->mangas,

                'currentPage' =>
                    1,

                'compteur' =>
                    1,

                'totalSeries' =>
                    $data->totalSeries,

                'perPage' =>
                    $data->perPage,

                'slugFilter' =>
                    $data->slugFilter,
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Show manga
    |--------------------------------------------------------------
    */

    public function show(
        string $slug,
        int $numero,
    ): never {

        $data =
            $this->mangaReadService
                ->one(
                    $slug,
                    $numero,
                );

        if ($data === null) {

            throw new NotFoundException(
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
                'manga' =>
                    $data->manga,
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Create page
    |--------------------------------------------------------------
    */

    public function create(): never
    {
        $this->title =
            'Manga | Ajouter';

        $this->render(
            'manga/ajouter',
        );
    }

    /*
    |--------------------------------------------------------------
    | Edit page
    |--------------------------------------------------------------
    */

    public function edit(
        string $slug,
        int $numero,
    ): never {

        $data =
            $this->mangaReadService
                ->one(
                    $slug,
                    $numero,
                );

        if ($data === null) {

            throw new NotFoundException(
                'Manga introuvable',
            );
        }

        $this->redirectToCanonicalUrl(
            $slug,
            $data->canonicalSlug,
            self::EDIT_PATH,
            $numero,
        );

        $this->title =
            'Manga | Modifier';

        $this->render(
            'manga/modifier',
            [
                'manga' =>
                    $data->manga,
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Store
    |--------------------------------------------------------------
    */

    public function store(
        MangaCreateRequest $request,
    ): never {

        if ($request->fails()) {

            throw new ValidationException(
                $request->errors(),
            );
        }

        $result =
            $this->mangaWriteService
                ->create(
                    $request->dto(),
                    $request->files(),
                );

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }

    /*
    |--------------------------------------------------------------
    | Update
    |--------------------------------------------------------------
    */

    public function update(
        MangaUpdateRequest $request,
        string $slug,
        int $numero,
    ): never {

        $data =
            $this->mangaReadService
                ->one(
                    $slug,
                    $numero,
                );

        if ($data === null) {

            throw new NotFoundException(
                'Manga introuvable',
            );
        }

        $redirectPath =
            $this->buildEditPath(
                $data->canonicalSlug,
                $numero,
            );

        if (
            $slug !==
            $data->canonicalSlug
        ) {

            throw new BaseHttpException(
                message:
                    'URL non canonique',

                statusCode: 409,

                data: [
                    'redirect' =>
                        $redirectPath,
                ],
            );
        }

        if ($request->fails()) {

            throw new ValidationException(
                $request->errors(),
            );
        }

        $result =
            $this->mangaWriteService
                ->update(
                    $data->canonicalSlug,
                    $numero,
                    $request->dto(),
                );

        if (!$result->success) {

            throw new BaseHttpException(
                message:
                    $result->message,

                statusCode: 422,
            );
        }

        $this->redirectWithSuccess(
            sprintf(
                '%s/%s/%d',
                self::SERIES_PATH,
                rawurlencode(
                    $data->canonicalSlug,
                ),
                $numero,
            ),
            $result->message,
        );
    }
}