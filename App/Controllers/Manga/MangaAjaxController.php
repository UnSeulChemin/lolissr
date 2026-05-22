<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Common\ServiceResult;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    private const AJAX_PATH = 'manga/ajax';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function ensureAjax(): void
    {
        if ($this->isAjax()) {
            return;
        }

        if (App::isTesting()) {
            return;
        }

        $this->error(
            'Requête AJAX requise',
            400,
        );
    }

    private function error(
        string $message,
        int $status = 400,
        array $data = [],
    ): never {
        $this->json(
            ServiceResult::error(
                message: $message,
                data: $data,
                status: $status,
            )->toArray(),
            $status,
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validationError(
        array $errors,
    ): never {
        $this->error(
            'Formulaire invalide',
            422,
            [
                'errors' => $errors,
            ],
        );
    }

    private function canonicalRedirect(
        string $action,
        string $slug,
        int $numero,
    ): string {
        return sprintf(
            '%s/%s/%s/%d',
            $this->basePath,
            self::AJAX_PATH,
            $action,
            rawurlencode($slug),
            $numero,
        );
    }

    /**
     * @return object{
     *     manga: object,
     *     canonicalSlug: string
     * }
     */
    private function resolveMangaOrFail(
        string $action,
        string $slug,
        int $numero,
    ): object {
        $data = $this->mangaReadService
            ->one(
                $slug,
                $numero,
            );

        if ($data === null) {
            $this->error(
                'Manga introuvable',
                404,
            );
        }

        if ($slug !== $data->canonicalSlug) {
            $this->error(
                'URL non canonique',
                409,
                [
                    'redirect' => $this->canonicalRedirect(
                        $action,
                        $data->canonicalSlug,
                        $numero,
                    ),
                ],
            );
        }

        return $data;
    }

    public function seriesPage(
        string $page = '1',
    ): never {
        $this->ensureAjax();

        $data = $this->mangaReadService
            ->series($page);

        if ($data === null) {
            $this->error(
                'Page introuvable',
                404,
            );
        }

        $this->renderPartial(
            'manga/partials/series_ajax',
            [
                'mangas' => $data->mangas,
                'compteur' => $data->compteur,
                'currentPage' => $data->currentPage,
                'slugFilter' => $data->slugFilter,
            ],
        );
    }

    public function search(
        string $query = '',
    ): never {
        $this->ensureAjax();

        $results = $this->mangaReadService
            ->searchResults($query);

        $this->json(
            ServiceResult::success(
                data: [
                    'results' => $results,
                ],
            )->toArray(),
        );
    }

    public function updateNote(
        MangaUpdateNoteRequest $request,
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail(
            'update-note',
            $slug,
            $numero,
        );

        if ($request->fails()) {
            $this->validationError(
                $request->errors(),
            );
        }

        $result = $this->mangaWriteService
            ->updateNote(
                $data->canonicalSlug,
                $numero,
                $request->dto(),
            );

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }

    public function updateReadStatus(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail(
            'update-read-status',
            $slug,
            $numero,
        );

        $result = $this->mangaWriteService
            ->updateReadStatus(
                $data->canonicalSlug,
                $numero,
                $this->request->integer(
                    'readStatus',
                    0,
                ),
            );

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }

    public function delete(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        $data = $this->resolveMangaOrFail(
            'delete',
            $slug,
            $numero,
        );

        $result = $this->mangaWriteService
            ->delete(
                $data->canonicalSlug,
                $numero,
            );

        $this->json(
            $result->toArray(),
            $result->status,
        );
    }
}