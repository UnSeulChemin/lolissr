<?php

declare(strict_types=1);

namespace App\Controllers\Manga;

use App\Controllers\Controller;
use App\Http\Requests\Manga\MangaUpdateNoteRequest;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Application\App;
use Framework\Http\Request;

final class MangaAjaxController extends Controller
{
    private const AJAX_PATH = 'manga/ajax/';

    public function __construct(
        protected MangaReadService $mangaReadService,
        protected MangaWriteService $mangaWriteService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    private function ensureAjax(): void
    {
        // Correction :
        // utilise maintenant le helper global isAjax()
        // pour garder une logique AJAX unique dans tout le projet.
        if ($this->isAjax()) {
            return;
        }

        // Très bonne idée déjà présente :
        // bypass spécial pour les tests automatisés.
        if (
            App::isTesting()
            && str_contains(
                $this->request->userAgent(),
                'LoliSSR-TestRunner',
            )
        ) {
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
        ?string $redirect = null,
    ): never {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // Très bon pattern :
        // redirect optionnel seulement quand nécessaire.
        if ($redirect !== null) {
            $response['redirect'] = $redirect;
        }

        $this->json(
            $response,
            $status,
        );
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function validationError(
        array $errors,
    ): never {
        // Correction :
        // centralise les erreurs de validation AJAX.
        // Tu garantis une API uniforme partout.
        $this->json([
            'success' => false,
            'message' => 'Formulaire invalide',
            'errors' => $errors,
        ], 422);
    }

    private function canonicalRedirect(
        string $action,
        string $slug,
        int $numero,
    ): string {
        // Correction :
        // suppression du hardcoded path.
        return $this->basePath
            . self::AJAX_PATH
            . $action
            . '/'
            . rawurlencode($slug)
            . '/'
            . $numero;
    }

    /**
     * Résout un manga ou retourne une erreur AJAX.
     */
    private function resolveMangaOrFail(
        string $action,
        string $slug,
        int $numero,
    ): object {
        // Correction importante :
        // toute cette logique était répétée 3x.
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

        // Très bon comportement déjà existant :
        // gestion des URLs canoniques même en AJAX.
        if ($slug !== $data->canonicalSlug) {
            $this->error(
                'URL non canonique',
                409,
                $this->canonicalRedirect(
                    $action,
                    $data->canonicalSlug,
                    $numero,
                ),
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
            ->searchAjax($query);

        $this->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function updateNote(
        MangaUpdateNoteRequest $request,
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        // Correction :
        // factorisation du fetch + canonical check.
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

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            ...$result->data,
        ], $result->status);
    }

    public function updateLu(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        // Même logique factorisée ici.
        $data = $this->resolveMangaOrFail(
            'update-lu',
            $slug,
            $numero,
        );

        $result = $this->mangaWriteService
            ->updateLu(
                $data->canonicalSlug,
                $numero,
                $this->request->integer(
                    'lu',
                    0,
                ),
            );

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
            'lu' => $result->lu,
        ], $result->status);
    }

    public function delete(
        string $slug,
        int $numero,
    ): never {
        $this->ensureAjax();

        // Même logique factorisée ici aussi.
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

        $this->json([
            'success' => $result->success,
            'message' => $result->message,
        ], $result->status);
    }
}