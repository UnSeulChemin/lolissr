<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use Throwable;

final class MangaAjaxController extends Controller
{
    private const AJAX_PATH =
        'manga/ajax';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function ensureAjax(): void
    {
        if (
            $this->isAjax()
            || App::isTesting()
        ) {
            return;
        }

        throw new BaseHttpException(
            'Requête AJAX requise',
            400,
        );
    }

    // -----------------------------
    // AJAX Search
    // -----------------------------
    public function search(
        string $query = '',
    ): never {

        $this->ensureAjax();

        try {

            $searchData =
                $this->mangaReadService
                    ->search($query);

            $this->json(
                ServiceResult::success(
                    data: [
                        'results' => $searchData->mangas,
                    ],
                ),
            );

        } catch (Throwable $exception) {

            $this->json(
                ServiceResult::error(
                    message:
                        $exception->getMessage(),

                    status:
                        500,
                ),
            );
        }
    }

    // -----------------------------
    // AJAX Series Page
    // -----------------------------
    public function seriesPage(
        string|int $page = 1,
    ): never {

        $this->ensureAjax();

        $page = max(
            1,
            (int) $page,
        );

        $data =
            $this->mangaReadService
                ->series($page);

        if ($data === null) {
            throw new NotFoundException(
                'Page introuvable',
            );
        }

        $this->renderPartial(
            'components/manga/series_ajax',
            [
                'mangas' =>
                    $data->mangas,

                'currentPage' =>
                    $data->currentPage,

                'totalPages' =>
                    $data->compteur,

                'slugFilter' =>
                    $data->slugFilter,

                'isSerieView' =>
                    $data->slugFilter !== null,

                'baseUri' =>
                    $this->baseUri,
            ],
        );
    }

    // -----------------------------
    // Update note
    // -----------------------------
    public function updateNote(
        string $slug,
        int $numero,
    ): never {

        $this->ensureAjax();

        $data =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $jacquette =
            (int) $this->request
                ->input(
                    'jacquette',
                    0,
                );

        $livreNote =
            (int) $this->request
                ->input(
                    'livre_note',
                    0,
                );

        if (
            $jacquette < 1
            || $jacquette > 5
            || $livreNote < 1
            || $livreNote > 5
        ) {

            $this->json(
                ServiceResult::error(
                    message:
                        'Valeurs invalides',

                    status:
                        400,
                ),
            );
        }

        $dto =
            MangaUpdateNoteDTO::fromArray([
                'jacquette' => $jacquette,
                'livre_note' => $livreNote,
            ]);

        $result =
            $this->mangaWriteService
                ->updateNote(
                    $data->canonicalSlug,
                    $numero,
                    $dto,
                );

        $responseData =
            $result->data;

        $responseData['notes'] = [
            'jacquette' => $jacquette,
            'livreNote' => $livreNote,
            'note' => $jacquette + $livreNote,
        ];

        $this->json(
            ServiceResult::success(
                message:
                    $result->message,

                data:
                    $responseData,

                status:
                    $result->status,
            ),
        );
    }

    // -----------------------------
    // Update read status
    // -----------------------------
    public function updateReadStatus(
        string $slug,
        int $numero,
    ): never {

        $this->ensureAjax();

        $data =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $readStatus =
            (int) $this->request
                ->input(
                    'readStatus',
                    0,
                );

        $result =
            $this->mangaWriteService
                ->updateReadStatus(
                    $data->canonicalSlug,
                    $numero,
                    $readStatus,
                );

        $this->json($result);
    }

    // -----------------------------
    // Delete manga
    // -----------------------------
    public function delete(
        string $slug,
        int $numero,
    ): never {

        $this->ensureAjax();

        $data =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $result =
            $this->mangaWriteService
                ->delete(
                    $data->canonicalSlug,
                    $numero,
                );

        $seriesStillExists =
            $this->mangaReadService
                ->seriesExists(
                    $data->canonicalSlug,
                );

        $baseUri =
            rtrim(
                $this->baseUri,
                '/',
            );

        $redirect =
            $seriesStillExists
                ? sprintf(
                    '%s/manga/series/%s',
                    $baseUri,
                    rawurlencode(
                        $data->canonicalSlug,
                    ),
                )
                : sprintf(
                    '%s/manga/series',
                    $baseUri,
                );

        $responseData =
            $result->data;

        $responseData['redirect']
            = $redirect;

        $this->json(
            ServiceResult::success(
                message:
                    $result->message,

                data:
                    $responseData,

                status:
                    $result->status,
            ),
        );
    }

    // -----------------------------
    // Resolve manga or fail
    // -----------------------------
    private function resolveMangaOrFail(
        string $slug,
        int $numero,
    ): object {

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

        if (
            $slug
            !== $data->canonicalSlug
        ) {
            throw new BaseHttpException(
                message:
                    'URL non canonique',

                statusCode:
                    409,

                data: [
                    'redirect' => sprintf(
                        '%s/%s/%s/%d',
                        rtrim(
                            $this->baseUri,
                            '/',
                        ),
                        self::AJAX_PATH,
                        rawurlencode(
                            $data->canonicalSlug,
                        ),
                        $numero,
                    ),
                ],
            );
        }

        return $data;
    }
}