<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\DTO\Manga\Inputs\MangaUpdateNoteDTO;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Exceptions\ValidationException;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    private const AJAX_PATH =
        'manga/ajax';

    private const SERIES_PATH =
        'manga/series';

    public function __construct(
        private readonly MangaReadService $mangaReadService,
        private readonly MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct(
            $request,
        );
    }

    /*
    |--------------------------------------------------------------
    | AJAX Search
    |--------------------------------------------------------------
    */

    public function search(
        string $query = '',
    ): never {

        $searchData =
            $this->mangaReadService
                ->search($query);

        $this->jsonResult(
            ServiceResult::success(
                data: [
                    'results' =>
                        $searchData->mangas,
                ],
            ),
        );
    }

    /*
    |--------------------------------------------------------------
    | AJAX Series Page
    |--------------------------------------------------------------
    */

    public function seriesPage(
        string|int $page = 1,
    ): never {

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

        $this->renderFragment(
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
            ],
        );
    }

    /*
    |--------------------------------------------------------------
    | Update Note
    |--------------------------------------------------------------
    */

    public function updateNote(
        string $slug,
        int $numero,
    ): never {

        $manga =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $jacquette =
            (int) $this->request->input(
                'jacquette',
                0,
            );

        $livreNote =
            (int) $this->request->input(
                'livre_note',
                0,
            );

        $this->validateNote(
            $jacquette,
            $livreNote,
        );

        $dto =
            MangaUpdateNoteDTO::fromArray([
                'jacquette' =>
                    $jacquette,

                'livre_note' =>
                    $livreNote,
            ]);

        $result =
            $this->mangaWriteService
                ->updateNote(
                    $manga->canonicalSlug,
                    $numero,
                    $dto,
                );

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $result->message,

                data: [
                    ...$result->data,

                    'notes' => [
                        'jacquette' =>
                            $jacquette,

                        'livreNote' =>
                            $livreNote,

                        'note' =>
                            $jacquette + $livreNote,
                    ],
                ],

                status:
                    $result->status,
            ),
        );
    }

    /*
    |--------------------------------------------------------------
    | Update Read Status
    |--------------------------------------------------------------
    */

    public function updateReadStatus(
        string $slug,
        int $numero,
    ): never {

        $manga =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $readStatus =
            (int) $this->request->input(
                'readStatus',
                0,
            );

        $result =
            $this->mangaWriteService
                ->updateReadStatus(
                    $manga->canonicalSlug,
                    $numero,
                    $readStatus,
                );

        $this->jsonResult(
            $result,
        );
    }

    /*
    |--------------------------------------------------------------
    | Delete Manga
    |--------------------------------------------------------------
    */

    public function delete(
        string $slug,
        int $numero,
    ): never {

        $manga =
            $this->resolveMangaOrFail(
                $slug,
                $numero,
            );

        $result =
            $this->mangaWriteService
                ->delete(
                    $manga->canonicalSlug,
                    $numero,
                );

        $seriesStillExists =
            $this->mangaReadService
                ->seriesExists(
                    $manga->canonicalSlug,
                );

        $redirect =
            $seriesStillExists
                ? sprintf(
                    '%s/%s/%s',
                    $this->baseUri,
                    self::SERIES_PATH,
                    rawurlencode(
                        $manga->canonicalSlug,
                    ),
                )
                : sprintf(
                    '%s/%s',
                    $this->baseUri,
                    self::SERIES_PATH,
                );

        $this->jsonResult(
            ServiceResult::success(
                message:
                    $result->message,

                data: [
                    ...$result->data,

                    'redirect' =>
                        $redirect,
                ],

                status:
                    $result->status,
            ),
        );
    }

    /*
    |--------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------
    */

    private function validateNote(
        int $jacquette,
        int $livreNote,
    ): void {

        if (
            $jacquette < 1
            || $jacquette > 5
        ) {

            throw new ValidationException([
                'jacquette' =>
                    'Note invalide',
            ]);
        }

        if (
            $livreNote < 1
            || $livreNote > 5
        ) {

            throw new ValidationException([
                'livre_note' =>
                    'Note invalide',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------
    | Resolve Manga
    |--------------------------------------------------------------
    */

    private function resolveMangaOrFail(
        string $slug,
        int $numero,
    ): object {

        $manga =
            $this->mangaReadService
                ->one(
                    $slug,
                    $numero,
                );

        if ($manga === null) {

            throw new NotFoundException(
                'Manga introuvable',
            );
        }

        if (
            $slug !==
            $manga->canonicalSlug
        ) {

            throw new BaseHttpException(
                message:
                    'URL non canonique',

                statusCode:
                    409,

                data: [
                    'redirect' => sprintf(
                        '%s/%s/%d',
                        self::SERIES_PATH,
                        rawurlencode(
                            $manga->canonicalSlug,
                        ),
                        $numero,
                    ),
                ],
            );
        }

        return $manga;
    }
}