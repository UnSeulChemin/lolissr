<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\DTO\Common\ServiceResult;
use App\Http\Controllers\Controller;
use App\Services\Artbook\ArtbookReadService;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Figurine\FigurineReadService;
use App\Services\Manga\MangaReadService;
use App\Services\Nendoroid\NendoroidReadService;
use App\Services\Peluche\PelucheReadService;

use Framework\Debug\Profiler;
use Framework\Http\Requests\Request;

final class GlobalSearchController extends Controller
{
    public function __construct(
        private readonly MangaReadService $mangas,
        private readonly ArtbookReadService $artbooks,
        private readonly ChinoisReadService $chinois,
        private readonly FigurineReadService $figurines,
        private readonly NendoroidReadService $nendoroids,
        private readonly PelucheReadService $peluches,
        Request $request
    )
    {
        parent::__construct($request);
    }

    public function search(): never
    {
        $query = trim($this->stringInput('q'));
        $this->jsonResult(ServiceResult::success(data: [
            'mangas' => Profiler::measure('search.mangas', fn (): array => $this->mangas->search($query, 5)->results),
            'artbooks' => Profiler::measure('search.artbooks', fn (): array => $this->artbooks->search($query, 5)->results),
            'chinois' => Profiler::measure('search.chinois', fn (): array => $this->chinois->search($query, 5)->results),
            'figurines' => Profiler::measure('search.figurines', fn (): array => $this->figurines->search($query, 5)->results),
            'nendoroids' => Profiler::measure('search.nendoroids', fn (): array => $this->nendoroids->search($query, 5)->results),
            'peluches' => Profiler::measure('search.peluches', fn (): array => $this->peluches->search($query, 5)->results)
        ]));
    }
}
