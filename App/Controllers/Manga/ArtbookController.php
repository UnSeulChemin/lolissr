<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\DTO\Manga\Responses\ArtbookData;
use App\Http\Requests\Manga\ArtbookCreateRequest;
use App\Http\Requests\Manga\ArtbookUpdateRequest;
use App\Services\Manga\ArtbookReadService;
use App\Services\Manga\ArtbookWriteService;

use Framework\Exceptions\BaseHttpException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class ArtbookController extends Controller
{
    private const ARTBOOKS_PATH = 'manga/artbooks';

    public function __construct(
        private readonly ArtbookReadService $artbookReadService,
        private readonly ArtbookWriteService $artbookWriteService,
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // PAGES
    // =========================================

    public function index(int $page = 1): never
    {
        $data = $this->artbookReadService->artbooks($page);

        if ($data === null)
        {
            throw new NotFoundException('Page introuvable');
        }

        $this->title =
            'Manga | Artbooks'
            . ($data->currentPage > 1
                ? ' - Page ' . $data->currentPage
                : '');

        $this->render(
            'pages/manga/artbooks/index',
            [
                'artbooks' => $data->artbooks,
                'currentPage' => $data->currentPage,
                'totalArtbooks' => $data->totalArtbooks,
                'perPage' => $data->perPage,
                'totalPages' => $data->totalPages,
            ]
        );
    }


    public function show(string $slug, int $numero): never
    {
        $artbook = $this->resolveOrFail($slug, $numero);

        $this->title = 'Artbook | ' . $artbook->artbook;

        $this->render(
            'pages/manga/artbooks/livre',
            [
                'artbook' => $artbook,
            ]
        );
    }


    // =========================================
    // CREATE
    // =========================================

    public function create(): never
    {
        $this->title = 'Manga | Ajouter un artbook';

        $this->render(
            'pages/manga/ajouter/artbook',
            [
                'form' => $this->formViewData(
                    'manga/ajouter/artbook',
                    'manga'
                ),
            ]
        );
    }


    public function store(
        ArtbookCreateRequest $request
    ): never {
        $this->validateRequest($request);

        $this->jsonResult(
            $this->artbookWriteService->create(
                $request->dto(),
                $request->files()
            )
        );
    }


    // =========================================
    // UPDATE
    // =========================================

    public function edit(
        string $slug,
        int $numero
    ): never {
        $artbook = $this->resolveOrFail($slug, $numero);

        $this->title = 'Artbook | ' . $artbook->artbook;

        $this->render(
            'pages/manga/artbooks/modifier',
            [
                'artbook' => $artbook,
                'form' => $this->formViewData(
                    sprintf(
                        '%s/%s/modifier/%d',
                        self::ARTBOOKS_PATH,
                        rawurlencode($artbook->slug),
                        $artbook->numero
                    ),
                    $this->artbookUrl(
                        $artbook->slug,
                        $artbook->numero
                    )
                ),
            ]
        );
    }


    public function update(
        ArtbookUpdateRequest $request,
        string $slug,
        int $numero
    ): never {
        $artbook = $this->resolveOrFail($slug, $numero);

        $this->validateRequest($request);

        $result = $this->artbookWriteService->update(
            $artbook->slug,
            $artbook->numero,
            $request->dto()
        );

        if (! $result->success)
        {
            throw new BaseHttpException(
                message: $result->message,
                statusCode: 422,
                data: $result->data
            );
        }

        $this->redirectWithSuccess(
            $this->artbookUrl(
                $artbook->slug,
                $artbook->numero
            ),
            $result->message
        );
    }


    // =========================================
    // HELPERS
    // =========================================

    private function artbookUrl(
        string $slug,
        int $numero
    ): string {
        return sprintf(
            '%s/%s/%d',
            self::ARTBOOKS_PATH,
            rawurlencode($slug),
            $numero
        );
    }


    private function resolveOrFail(
        string $slug,
        int $numero
    ): ArtbookData {
        return $this->artbookReadService->one(
            $slug,
            $numero
        )
        ?? throw new NotFoundException(
            'Artbook introuvable'
        );
    }
}