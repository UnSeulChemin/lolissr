<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

use Framework\Http\Exceptions\NotFoundException;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
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
    // SEARCH
    // =========================================

    public function search(string|int $query = ''): never
    {
        $searchData = $this->mangaReadService->search(
            (string) $query
        );

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ]
            )
        );
    }


    // =========================================
    // SERIES PAGE
    // =========================================

    public function seriesPage(int $page = 1): never
    {
        $page = max(1, $page);

        $data = $this->mangaReadService->series($page);

        if ($data === null)
        {
            throw new NotFoundException(
                'Page introuvable'
            );
        }

        $this->renderFragment(
            'pages/manga/series/ajax',
            [
                'mangas' => $data->mangas,
                'currentPage' => $data->currentPage,
                'totalPages' => $data->totalPages,
                'slugFilter' => $data->slugFilter,
                'isSerieView' => $data->slugFilter !== null,
            ]
        );
    }


    // =========================================
    // UPDATE NOTE
    // =========================================

    public function updateNote(
        MangaUpdateNoteRequest $request,
        string $slug,
        int $numero
    ): never {
        $this->validateRequest($request);

        $this->jsonResult($this->mangaWriteService->updateNote(
            $slug,
            $numero,
            $request->dto()
        ));
    }

    // =========================================
    // UPDATE READ STATUS
    // =========================================

    public function updateReadStatus(
        string $slug,
        int $numero
    ): never {
        $readStatus = $this->binaryStatusInput('readStatus');

        $result = $this->mangaWriteService->updateReadStatus(
            $slug,
            $numero,
            $readStatus
        );

        $this->jsonResult($result);
    }


    // =========================================
    // DELETE
    // =========================================

    public function delete(
        string $slug,
        int $numero
    ): never {
        $result = $this->mangaWriteService->delete(
            $slug,
            $numero
        );

        if (! $result->success)
        {
            $this->jsonResult($result);
        }

        $seriesStillExists = $this->mangaReadService->seriesExists(
            $slug
        );

        $redirect = $this->buildRedirectPath(
            $slug,
            $seriesStillExists
        );

        $this->jsonResult(
            ServiceResult::success(
                message: $result->message,
                data: [
                    ...$result->data,
                    'redirect' => $redirect,
                ],
                status: $result->status
            )
        );
    }


    // =========================================
    // HELPERS
    // =========================================

    private function buildRedirectPath(
        string $slug,
        bool $seriesStillExists
    ): string {
        return $seriesStillExists
            ? sprintf(
                '%s/%s/%s',
                $this->baseUri,
                self::SERIES_PATH,
                rawurlencode(\Framework\Support\Str::slug($slug))
            )
            : sprintf(
                '%s/%s',
                $this->baseUri,
                self::SERIES_PATH
            );
    }
}
