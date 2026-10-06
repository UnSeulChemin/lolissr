<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manga;

use App\DTO\Manga\Responses\MangaDetailData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manga\MangaCreateRequest;
use App\Http\Requests\Manga\MangaUpdateRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

use Framework\Http\Exceptions\BaseHttpException;
use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Requests\Request;

final class MangaController extends Controller
{
    private const SERIES_PATH = 'manga/series';

    public function acquireRelease(string $slug, int $numero): never
    {
        $result = $this->mangaWriteService->acquireRelease($slug, $numero, $this->upcomingMangaService);
        if ($this->expectsJson()) $this->jsonResult($result);
        if (!$result->success) $this->redirectWithError(self::SERIES_PATH . '/' . rawurlencode($slug), $result->message, false);
        $this->redirectWithSuccess(self::SERIES_PATH . '/' . rawurlencode($slug), $result->message);
    }

    public function forthcoming(int $page = 1): never
    { $this->releases('a-paraitre', $page); }

    public function missing(int $page = 1): never
    { $this->releases('non-possedes', $page); }
    public function releases(string $type, int $page = 1): never
    {
        if (!in_array($type, ['a-paraitre', 'non-possedes'], true)) throw new NotFoundException('Page introuvable');
        $items = array_values(array_filter($this->upcomingMangaService->all(), static fn ($release): bool => $release->isUpcoming === ($type === 'a-paraitre')));
        $items = array_reverse($items);
        $perPage = max(1, \Framework\Config\ApplicationConfig::pagination());
        $totalPages = max(1, (int) ceil(count($items) / $perPage));
        if ($page < 1 || $page > $totalPages) throw new NotFoundException('Page introuvable');
        $this->title = $type === 'a-paraitre' ? 'Manga | À paraître' : 'Manga | Non possédés';
        $this->render('pages/manga/series/releases', ['releases' => array_slice($items, ($page - 1) * $perPage, $perPage), 'currentPage' => $page, 'totalPages' => $totalPages, 'paginationPath' => 'manga/series/' . $type]);
    }

    public function __construct(
        private readonly MangaReadService $mangaReadService,
        private readonly MangaWriteService $mangaWriteService,
        private readonly \App\Services\Manga\UpcomingMangaService $upcomingMangaService,
        Request $request
    )
    {
        parent::__construct($request);
    }

    // =================================================
    // PAGES PUBLIQUES
    // =================================================

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

        $this->title =
            'Manga | Séries'
            . ($data->currentPage > 1 ? ' - Page ' . $data->currentPage : '');

        $this->render(
            'pages/manga/series/index',
            [
                'mangas' => $data->mangas,
                'currentPage' => $data->currentPage,
                'totalSeries' => $data->totalSeries,
                'perPage' => $data->perPage,
                'slugFilter' => $data->slugFilter,
                'totalPages' => $data->totalPages
            ]
        );
    }

    public function ajouter(): never
    {
        $this->title = 'Manga | Ajouter';

        $this->render('pages/manga/create-choice');
    }

    public function links(): never
    {
        $this->title = 'Manga | Liens utiles';

        $this->render('pages/manga/links');
    }

    public function notes(int $page = 1): never
    {
        $data = $this->mangaReadService->notes($page);
        if ($data === null) throw new NotFoundException('Page introuvable');

        $this->title = 'Manga | Notes';

        $this->render(
            'pages/manga/series/notes',
            ['mangas' => $data->mangas, 'currentPage' => $data->currentPage, 'totalPages' => $data->totalPages]
        );
    }

    public function aLire(int $page = 1): never
    {
        $data = $this->mangaReadService->aLire($page);
        if ($data === null) throw new NotFoundException('Page introuvable');

        $this->title = 'Manga | À lire';

        $this->render(
            'pages/manga/series/unread',
            ['mangas' => $data->mangas, 'currentPage' => $data->currentPage, 'totalPages' => $data->totalPages]
        );
    }

    // =================================================
    // AFFICHAGE
    // =================================================

    public function showSeries(string $slug, int $page = 1): never
    {
        $data = $this->mangaReadService->showSeries($slug, $page);

        if ($data === null)
        {
            throw new NotFoundException('Manga introuvable');
        }

        $this->title = 'Manga | ' . $data->mangas[0]->livre;

        $this->render(
            'pages/manga/series/index',
            [
                'mangas' => $data->mangas,
                'currentPage' => $data->currentPage,
                'totalSeries' => $data->totalSeries,
                'perPage' => $data->perPage,
                'slugFilter' => $data->slugFilter,
                'totalPages' => $data->totalPages,
                'upcoming' => $this->upcomingMangaService->forSeries($slug, $page, $data->perPage)
            ]
        );
    }

    public function showManga(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render('pages/manga/series/show', ['manga' => $data->manga]);
    }

    // =================================================
    // AJOUT
    // =================================================

    public function create(): never
    {
        $this->title = 'Manga | Ajouter un manga';

        $this->render('pages/manga/create', [
            'form' => $this->formViewData('manga/ajouter/manga', 'manga'),
            'existingSeries' => $this->mangaReadService->seriesForCreate()
        ]);
    }

    // =================================================
    // MODIFICATION
    // =================================================

    public function edit(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $this->title = 'Manga | ' . $data->manga->livre;

        $this->render(
            'pages/manga/series/edit',
            [
                'manga' => $data->manga,
                'form' => $this->formViewData(
                    sprintf('%s/%s/modifier/%d', self::SERIES_PATH, rawurlencode($data->manga->slug), $numero),
                    $this->mangaUrl($data->manga->slug, $numero)
                )
            ]
        );
    }

    // =================================================
    // TRAITEMENTS
    // =================================================

    public function store(MangaCreateRequest $request): never
    {
        $this->validateRequest($request);

        $this->jsonResult($this->mangaWriteService->create($request->dto(), $request->files()));
    }

    public function update(MangaUpdateRequest $request, string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $this->validateRequest($request);

        $result = $this->mangaWriteService->update($data->manga->slug, $numero, $request->dto());

        if (! $result->success)
        {
            throw new BaseHttpException(message: $result->message, statusCode: $result->status, data: $result->data);
        }

        $this->redirectWithSuccess($this->mangaUrl($data->manga->slug, $numero), $result->message);
    }

    // =================================================
    // UTILITAIRES
    // =================================================

    private function mangaUrl(string $slug, int $numero): string
    {
        return sprintf('%s/%s/%d', self::SERIES_PATH, rawurlencode($slug), $numero);
    }

    private function resolveMangaOrFail(string $slug, int $numero): MangaDetailData
    {
        return $this->mangaReadService->one($slug, $numero)
        ?? throw new NotFoundException('Manga introuvable');
    }
}
