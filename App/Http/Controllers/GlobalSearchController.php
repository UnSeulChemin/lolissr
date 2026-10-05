<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Common\ServiceResult;
use App\Services\Artbook\ArtbookReadService;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Figurine\FigurineReadService;
use App\Services\Manga\MangaReadService;
use App\Services\Nendoroid\NendoroidReadService;
use App\Services\Peluche\PelucheReadService;

use Framework\Http\Request;
use Framework\Debug\Profiler;

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
            'mangas' => Profiler::measure('search.mangas', fn (): array => $this->mangas->search($query)->results),
            'artbooks' => Profiler::measure('search.artbooks', fn (): array => $this->artbooks->search($query)->results),
            'chinois' => Profiler::measure('search.chinois', fn (): array => $this->chinois->search($query)->results),
            'figurines' => Profiler::measure('search.figurines', fn (): array => $this->figurines->search($query)->results),
            'nendoroids' => Profiler::measure('search.nendoroids', fn (): array => $this->nendoroids->search($query)->results),
            'peluches' => Profiler::measure('search.peluches', fn (): array => $this->peluches->search($query)->results)
        ]));
    }
}
