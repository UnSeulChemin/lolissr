<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\DTO\Manga\Responses\MangaShowData;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;

use Framework\Exceptions\BaseHttpException;
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
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function buildRedirectPath(string $slug, bool $seriesStillExists): string
    {
        return $seriesStillExists
            ? sprintf('%s/%s/%s', $this->baseUri, self::SERIES_PATH, rawurlencode($slug))
            : sprintf('%s/%s', $this->baseUri, self::SERIES_PATH);
    }

    private function validateNote(
        int $note,
        string $field,
    ): void {
        if (
            $note < self::MIN_NOTE
            || $note > self::MAX_NOTE
        ) {
            throw new ValidationException([
                $field => 'Note invalide',
            ]);
        }
    }

    private function resolveMangaOrFail(string $slug, int $numero): MangaShowData
    {
        $data = $this->mangaReadService->one($slug, $numero);

        if ($data === null) {
            throw new NotFoundException('Manga introuvable');
        }

        if ($slug !== $data->canonicalSlug) {
            throw new BaseHttpException(
                message: 'URL non canonique',
                statusCode: 409,
                data: [
                    'redirect' => sprintf('%s/%s/%d', self::SERIES_PATH, rawurlencode($data->canonicalSlug), $numero),
                ],
            );
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Search
    |--------------------------------------------------------------------------
    */

    public function search(string $query = ''): never
    {
        $searchData = $this->mangaReadService->search($query);

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
    | AJAX Series Page
    |--------------------------------------------------------------------------
    */

    public function seriesPage(int $page = 1): never
    {
        $page = max(1, $page);
        $data = $this->mangaReadService->series($page);

        if ($data === null) {
            throw new NotFoundException('Page introuvable');
        }

        $this->renderFragment('pages/manga/series/ajax', [
            'mangas' => $data->mangas,
            'currentPage' => $data->currentPage,
            'totalPages' => $data->compteur,
            'slugFilter' => $data->slugFilter,
            'isSerieView' => $data->slugFilter !== null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Note
    |--------------------------------------------------------------------------
    */

    public function updateNote(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $jacquette = (int) $this->request->input('jacquette', 0);
        $livreNote = (int) $this->request->input('livre_note', 0);

        $this->validateNote(
            $jacquette,
            'jacquette',
        );

        $this->validateNote(
            $livreNote,
            'livre_note',
        );

        $dto = MangaUpdateNoteDTO::fromArray([
            'jacquette' => $jacquette,
            'livre_note' => $livreNote,
        ]);

        $result = $this->mangaWriteService->updateNote($data->canonicalSlug, $numero, $dto);

        $this->jsonResult(ServiceResult::success(
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
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Read Status
    |--------------------------------------------------------------------------
    */

    public function updateReadStatus(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);
        $readStatus = (int) $this->request->input('readStatus', 0);

        $result = $this->mangaWriteService->updateReadStatus($data->canonicalSlug, $numero, $readStatus);
        $this->jsonResult($result);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Manga
    |--------------------------------------------------------------------------
    */

    public function delete(string $slug, int $numero): never
    {
        $data = $this->resolveMangaOrFail($slug, $numero);

        $result = $this->mangaWriteService->delete($data->canonicalSlug, $numero);

        $seriesStillExists = $this->mangaReadService->seriesExists($data->canonicalSlug);
        $redirect = $this->buildRedirectPath($data->canonicalSlug, $seriesStillExists);

        $this->jsonResult(ServiceResult::success(
            message: $result->message,
            data: [...$result->data, 'redirect' => $redirect],
            status: $result->status
        ));
    }
}