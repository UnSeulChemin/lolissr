<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Responses\ArtbookData;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Responses\MangaShowData;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use App\Services\Manga\ArtbookReadService;
use App\Services\Manga\ArtbookWriteService;

use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    private const SERIES_PATH = 'manga/series';

    private const MIN_NOTE = 1;

    private const MAX_NOTE = 5;

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
    | AJAX SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): never
    {
        $searchData = $this->mangaReadService->search((string) $query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ],
            ),
        );
    }

    public function searchArtbooks(string|int $query = ''): never
    {
        $searchData = $this->artbookReadService->search((string) $query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' => $searchData->results,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SERIES PAGE
    |--------------------------------------------------------------------------
    */

    public function seriesPage(int $page = 1): never
    {
        $page = max(1, $page);

        $data = $this->mangaReadService->series($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment('pages/manga/series/ajax', [
            'mangas' => $data->mangas,
            'currentPage' => $data->currentPage,
            'totalPages' => $data->totalPages,
            'slugFilter' => $data->slugFilter,
            'isSerieView' => $data->slugFilter !== null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX ARTBOOKS PAGE
    |--------------------------------------------------------------------------
    */

    public function artbooksPage(int $page = 1): never
    {
        $page = max(1, $page);

        $data = $this->artbookReadService->artbooks($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment('pages/manga/artbooks/ajax', [
            'artbooks' => $data->artbooks,
            'currentPage' => $data->currentPage,
            'totalPages' => $data->totalPages,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE NOTE
    |--------------------------------------------------------------------------
    */

    public function updateNote(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $jacquette = (int) $this->request->input('jacquette', 0);
        $livreNote = (int) $this->request->input('livre_note', 0);

        $this->validateNote($jacquette, 'jacquette');
        $this->validateNote($livreNote, 'livre_note');

        $dto = MangaUpdateNoteDTO::fromArray([
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
        ]);

        $result = $this->mangaWriteService->updateNote($data->manga->slug, $numero, $dto);

        $this->jsonResult(
            ServiceResult::success(
                message: $result->message,
                data: [
                    ...$result->data,
                    'notes' => [
                        'jacquette' => $jacquette,
                        'livreNote' => $livreNote,
                        'note' => $jacquette + $livreNote,
                    ],
                ],
                status: $result->status
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE READ STATUS
    |--------------------------------------------------------------------------
    */

    public function updateReadStatus(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $readStatus = (int) $this->request->input('readStatus', 0);

        $result = $this->mangaWriteService->updateReadStatus(
            $data->manga->slug,
            $numero,
            $readStatus
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ARTBOOK READ STATUS
    |--------------------------------------------------------------------------
    */

    public function updateArtbookReadStatus(string $slug, int $numero): never
    {
        $artbook = $this->resolveArtbookOrFail($slug, $numero);

        $readStatus = (int) $this->request->input('readStatus', 0);

        $result = $this->artbookWriteService->updateReadStatus(
            $artbook->slug,
            $artbook->numero,
            $readStatus
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE MANGA
    |--------------------------------------------------------------------------
    */

    public function delete(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $result = $this->mangaWriteService->delete($data->manga->slug, $numero);

        $seriesStillExists = $this->mangaReadService->seriesExists($data->manga->slug);

        $redirect = $this->buildRedirectPath($data->manga->slug, $seriesStillExists);

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

    public function deleteArtbook(string $slug, int $numero): never
    {
        $artbook = $this->resolveArtbookOrFail($slug, $numero);

        $result = $this->artbookWriteService->delete(
            $artbook->slug,
            $artbook->numero
        );

        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function buildRedirectPath(string $slug, bool $seriesStillExists): string
    {
        return $seriesStillExists
            ? sprintf('%s/%s/%s', $this->baseUri, self::SERIES_PATH, rawurlencode($slug))
            : sprintf('%s/%s', $this->baseUri, self::SERIES_PATH);
    }

    private function validateNote(int $note, string $field): void
    {
        if ($note < self::MIN_NOTE || $note > self::MAX_NOTE)
        {
            throw new ValidationException([$field => 'Note invalide']);
        }
    }

    private function resolveMangaOrFail(string $slug, int $numero): MangaShowData
    {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null)
        {
            throw new NotFoundException('Manga introuvable');
        }

        return $data;
    }

    private function resolveArtbookOrFail(
        string $slug,
        int $numero
    ): ArtbookData
    {
        $artbook = $this->artbookReadService->one(
            $slug,
            $numero
        );

        if ($artbook === null)
        {
            throw new NotFoundException('Artbook introuvable');
        }

        return $artbook;
    }
}
