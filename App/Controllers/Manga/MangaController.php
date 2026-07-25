<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Manga\Responses\MangaShowData;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class MangaController extends Controller
{
    private const SERIES_PATH = 'manga/series';

    public function __construct(
        private readonly MangaReadService $mangaReadService,
        private readonly MangaWriteService $mangaWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // PAGES PUBLIQUES
    // =========================================

    public function index(): never
    {
        $this->title = 'Manga';

        $this->render(
            'pages/manga/index'
        );
    }

    public function series(
        int $page = 1
    ): never {
        $data = $this->mangaReadService->series($page);

        if ($data === null)
        {
            throw new NotFoundException(
                'Page introuvable'
            );
        }

        $this->title =
            'Manga | Series'
            . ($data->currentPage > 1
                ? ' - Page ' . $data->currentPage
                : '');

        $this->render(
            'pages/manga/series/index',
            [
                'mangas' => $data->mangas,
                'currentPage' => $data->currentPage,
                'totalSeries' => $data->totalSeries,
                'perPage' => $data->perPage,
                'slugFilter' => $data->slugFilter,
                'totalPages' => $data->totalPages,
            ]
        );
    }

    public function ajouter(): never
    {
        $this->title = 'Manga | Ajouter';

        $this->render(
            'pages/manga/ajouter/index'
        );
    }

    public function links(): never
    {
        $this->title = 'Manga | Liens utiles';

        $this->render(
            'pages/manga/lien'
        );
    }

    public function notes(): never
    {
        $this->title = 'Manga | Notes';

        $this->render(
            'pages/manga/series/notes',
            [
                'mangas' => $this->mangaReadService->notes(),
            ]
        );
    }

    public function aLire(): never
    {
        $this->title = 'Manga | À lire';

        $this->render(
            'pages/manga/series/a-lire',
            [
                'mangas' => $this->mangaReadService->aLire(),
            ]
        );
    }

    // =========================================
    // AFFICHAGE
    // =========================================

    public function showSeries(
        string $slug
    ): never {
        $data = $this->mangaReadService->showSeries($slug);

        if ($data === null)
        {
            throw new NotFoundException(
                'Manga introuvable'
            );
        }

        $this->title = 'Manga | ' . $data->mangas[0]->livre;

        $this->render(
            'pages/manga/series/index',
            [
                'mangas' => $data->mangas,
                'currentPage' => 1,
                'totalSeries' => $data->totalSeries,
                'perPage' => $data->perPage,
                'slugFilter' => $data->slugFilter,
                'totalPages' => $data->totalPages,
            ]
        );
    }

    public function showManga(
        string $slug,
        int $numero
    ): never {
        $data = $this->resolveMangaOrFail(
            $slug,
            $numero
        );

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render(
            'pages/manga/series/livre',
            [
                'manga' => $data->manga,
            ]
        );
    }

    // =========================================
    // AJOUT
    // =========================================

    public function create(): never
    {
        $this->title = 'Manga | Ajouter un manga';

        $this->render(
            'pages/manga/ajouter/manga',
            [
                'form' => $this->formViewData(
                    'manga/ajouter/manga',
                    'manga'
                ),
            ]
        );
    }

    // =========================================
    // MODIFICATION
    // =========================================

    public function edit(
        string $slug,
        int $numero
    ): never {
        $data = $this->resolveMangaOrFail(
            $slug,
            $numero
        );

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render(
            'pages/manga/series/modifier',
            [
                'manga' => $data->manga,
                'form' => $this->formViewData(
                    sprintf(
                        '%s/%s/modifier/%d',
                        self::SERIES_PATH,
                        rawurlencode($data->manga->slug),
                        $numero
                    ),
                    $this->mangaUrl(
                        $data->manga->slug,
                        $numero
                    )
                ),
            ]
        );
    }

    // =========================================
    // TRAITEMENTS
    // =========================================

    public function store(
        MangaCreateRequest $request
    ): never {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->mangaWriteService->create(
                $request->dto(),
                $request->files()
            )
        );
    }

    public function update(
        MangaUpdateRequest $request,
        string $slug,
        int $numero
    ): never {
        $data = $this->resolveMangaOrFail(
            $slug,
            $numero
        );

        $this->validateRequest($request);

        $result = $this->mangaWriteService->update(
            $data->manga->slug,
            $numero,
            $request->dto()
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data
            );
        }

        $this->redirectWithSuccess(
            $this->mangaUrl(
                $data->manga->slug,
                $numero
            ),
            $result->message
        );
    }

    // =========================================
    // HELPERS
    // =========================================

    private function mangaUrl(
        string $slug,
        int $numero
    ): string {
        return sprintf(
            '%s/%s/%d',
            self::SERIES_PATH,
            rawurlencode($slug),
            $numero
        );
    }

    private function resolveMangaOrFail(
        string $slug,
        int $numero
    ): MangaShowData {
        return $this->mangaReadService->one(
            $slug,
            $numero
        )
        ?? throw new NotFoundException(
            'Manga introuvable'
        );
    }
}