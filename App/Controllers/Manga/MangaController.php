<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Manga\Responses\ArtbookData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Http\Requests\Manga\ArtbookCreateRequest;
use App\Http\Requests\Manga\ArtbookUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use App\Services\Manga\ArtbookReadService;
use App\Services\Manga\ArtbookWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class MangaController extends Controller
{
    private const SERIES_PATH = 'manga/series';

    public function __construct(
        private readonly MangaReadService $mangaReadService,
        private readonly MangaWriteService $mangaWriteService,
        private readonly ArtbookReadService $artbookReadService,
        private readonly ArtbookWriteService $artbookWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES PUBLIQUES
    |--------------------------------------------------------------------------
    */

    public function index(): never
    {
        $this->title = 'Manga';

        $this->render('pages/manga/index');
    }

    public function series(int $page = 1): never
    {
        $data = $this->mangaReadService->series($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Manga | Series' . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render('pages/manga/series/index', [
            'mangas' => $data->mangas,
            'currentPage' => $data->currentPage,
            'compteur' => $data->compteur,
            'totalSeries' => $data->totalSeries,
            'perPage' => $data->perPage,
            'slugFilter' => $data->slugFilter,
        ]);
    }

    public function artbooks(int $page = 1): never
    {
        $data = $this->artbookReadService->artbooks($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title = 'Manga | Artbooks' . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render('pages/manga/artbooks/index', [
            'artbooks' => $data->artbooks,
            'currentPage' => $data->currentPage,
            'compteur' => $data->compteur,
            'totalArtbooks' => $data->totalArtbooks,
            'perPage' => $data->perPage,
        ]);
    }

    public function ajouter(): never
    {
        $this->title = 'Manga | Ajouter';

        $this->render('pages/manga/ajouter/index');
    }

    public function links(): never
    {
        $this->title = 'Manga | Liens utiles';

        $this->render('pages/manga/lien');
    }

    public function notes(): never
    {
        $this->title = 'Manga | Notes';

        $this->render('pages/manga/series/notes', ['mangas' => $this->mangaReadService->notes()]);
    }

    public function aLire(): never
    {
        $this->title = 'Manga | À lire';

        $this->render('pages/manga/series/a-lire', ['mangas' => $this->mangaReadService->aLire()]);
    }

    public function search(string $query = ''): never
    {
        $data = $this->mangaReadService->search($query);

        $this->title = $data->search !== ''
            ? 'Manga | Recherche : ' . $data->search
            : 'Manga | Recherche';

        $this->render('pages/manga/series/recherche', ['mangas' => $data->results, 'search' => $data->search]);
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHAGE
    |--------------------------------------------------------------------------
    */

    public function showSeries(string $slug): never
    {
        $data = $this->mangaReadService->showSeries($slug);

        if ($data === null || $data->mangas === [])
        {
            throw new NotFoundException('Manga introuvable');
        }

        $this->title = 'Manga | ' . $data->mangas[0]->livre;

        $this->render('pages/manga/series/index', [
            'mangas' => $data->mangas,
            'currentPage' => 1,
            'compteur' => 1,
            'totalSeries' => $data->totalSeries,
            'perPage' => $data->perPage,
            'slugFilter' => $data->slugFilter,
        ]);
    }

    public function showManga(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render('pages/manga/series/livre', ['manga' => $data->manga]);
    }

    public function showArtbook(string $slug, int $numero): never
    {
        $artbook = $this->resolveArtbookOrFail($slug, $numero);

        $this->title = 'Artbook | ' . $artbook->artbook;

        $this->render('pages/manga/artbooks/livre', ['artbook' => $artbook]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJOUT
    |--------------------------------------------------------------------------
    */

    public function createManga(): never
    {
        $this->title = 'Manga | Ajouter un manga';

        $this->render('pages/manga/ajouter/manga');
    }

    public function createArtbook(): never
    {
        $this->title = 'Manga | Ajouter un artbook';

        $this->render('pages/manga/ajouter/artbook');
    }

    /*
    |--------------------------------------------------------------------------
    | MODIFICATION
    |--------------------------------------------------------------------------
    */

    public function edit(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render('pages/manga/series/modifier', ['manga' => $data->manga]);
    }

    public function editArtbook(string $slug, int $numero): never
    {
        $artbook = $this->resolveArtbookOrFail($slug, $numero);

        $this->title = 'Artbook | ' . $artbook->artbook;

        $this->render('pages/manga/artbooks/modifier', ['artbook' => $artbook]);
    }

    /*
    |--------------------------------------------------------------------------
    | TRAITEMENTS
    |--------------------------------------------------------------------------
    */

    public function store(MangaCreateRequest $request): never
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }

        $result = $this->mangaWriteService->create($request->dto(), $request->files());

        $this->jsonResult($result);
    }

    public function storeArtbook(ArtbookCreateRequest $request): never
    {
        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }

        $result = $this->artbookWriteService->create($request->dto(), $request->files());

        $this->jsonResult($result);
    }

    public function update(MangaUpdateRequest $request, string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }

        $result = $this->mangaWriteService->update($data->canonicalSlug, $numero, $request->dto());

        if (! $result->success)
        {
            throw new BaseHttpException(message: $result->message, statusCode: 422, data: $result->data);
        }

        $this->redirectWithSuccess(
            sprintf(
                '%s/%s/%d',
                self::SERIES_PATH,
                rawurlencode($data->canonicalSlug),
                $numero
            ),
            $result->message
        );
    }

    public function updateArtbook(ArtbookUpdateRequest $request, string $slug, int $numero): never
    {
        $artbook = $this->resolveArtbookOrFail($slug, $numero);

        if ($request->fails())
        {
            throw new ValidationException($request->errors());
        }

        $result = $this->artbookWriteService->update($artbook->slug, $artbook->numero, $request->dto());

        if (! $result->success)
        {
            throw new BaseHttpException(message: $result->message, statusCode: 422, data: $result->data);
        }

        $this->redirectWithSuccess(
            sprintf(
                'manga/artbooks/%s/%d',
                rawurlencode($artbook->slug),
                $artbook->numero,
            ),
            $result->message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function resolveMangaOrFail(string $slug, int $numero): MangaShowData
    {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null)
        {
            throw new NotFoundException('Manga introuvable');
        }

        if ($slug !== $data->canonicalSlug)
        {
            throw new BaseHttpException(
                message: 'URL non canonique',
                statusCode: 409,
                data: [
                    'redirect' => sprintf(
                        '%s/%s/%d',
                        self::SERIES_PATH,
                        rawurlencode($data->canonicalSlug),
                        $numero
                    ),
                ]
            );
        }

        return $data;
    }

    private function resolveArtbookOrFail(string $slug, int $numero): ArtbookData
    {
        $artbook = $this->artbookReadService->one($slug, $numero);

        if ($artbook === null)
        {
            throw new NotFoundException('Artbook introuvable');
        }

        return $artbook;
    }
}
