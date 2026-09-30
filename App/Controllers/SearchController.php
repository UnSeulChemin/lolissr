<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Common\ServiceResult;
use App\Services\Chinois\ChinoisReadService;
use App\Services\Figurine\FigurineReadService;
use App\Services\Manga\ArtbookReadService;
use App\Services\Manga\MangaReadService;
use App\Services\Nendoroid\NendoroidReadService;
use App\Services\Peluche\PelucheReadService;
use Framework\Http\Request;

final class SearchController extends Controller
{
    public function __construct(
        private readonly MangaReadService $mangas,
        private readonly ArtbookReadService $artbooks,
        private readonly ChinoisReadService $chinois,
        private readonly FigurineReadService $figurines,
        private readonly NendoroidReadService $nendoroids,
        private readonly PelucheReadService $peluches,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function search(): never
    {
        $query = trim($this->stringInput('q'));
        $this->jsonResult(ServiceResult::success(data: [
            'mangas' => $this->mangas->search($query)->results,
            'artbooks' => $this->artbooks->search($query)->results,
            'chinois' => $this->chinois->search($query)->results,
            'figurines' => $this->figurines->search($query)->results,
            'nendoroids' => $this->nendoroids->search($query)->results,
            'peluches' => $this->peluches->search($query)->results,
        ]));
    }
}
