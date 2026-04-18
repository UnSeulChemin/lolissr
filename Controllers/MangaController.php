<?php

namespace App\Controllers;

use App\Core\Functions;
use App\Core\Logger;
use App\Core\Session;
use App\Core\Validator;
use App\Models\MangaModel;

class MangaController extends Controller
{
    /**
     * Retourne le modèle manga.
     */
    protected function mangaModel(): MangaModel
    {
        return new MangaModel();
    }

    /**
     * Vérifie si la requête est AJAX.
     */
    protected function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Retourne une réponse JSON.
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data);
        exit;
    }

    /**
     * Retourne true si le mode test upload est activé.
     */
    protected function isTestUploadMode(): bool
    {
        return (bool) Functions::env('TEST_UPLOAD_MODE', false);
    }

    /**
     * Retourne le dossier d’upload de test absolu.
     */
    protected function testUploadDirectory(): string
    {
        $directory = trim((string) Functions::env('TEST_UPLOAD_DIR', 'tests/tmp-uploads'), '/\\');

        return ROOT . '/' . $directory . '/';
    }

    /**
     * Convertit une note postée.
     * Retourne null si vide ou invalide.
     */
    protected function normalizePostedNote(?string $value): ?int
    {
        if ($value === null || trim($value) === '')
        {
            return null;
        }

        $value = (int) $value;

        if ($value < 1 || $value > 5)
        {
            return null;
        }

        return $value;
    }

    /**
     * Fabrique le validator.
     */
    protected function makeValidator(array $post, array $files): Validator
    {
        return new Validator($post, $files);
    }

    /**
     * Redirige vers l'URL canonique si le slug demandé n'est pas correct.
     */
    protected function redirectToCanonicalUrl(
        string $requestedSlug,
        string $canonicalSlug,
        string $pathPrefix,
        ?int $numero = null
    ): void
    {
        if ($requestedSlug === $canonicalSlug)
        {
            return;
        }

        $location = trim($pathPrefix, '/') . '/' . rawurlencode($canonicalSlug);

        if ($numero !== null)
        {
            $location .= '/' . $numero;
        }

        header('Location: ' . Functions::basePath() . '/' . $location, true, 301);
        exit;
    }

    /**
     * Accueil manga.
     * Route : /manga
     */
    public function index(): void
    {
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * Page lien.
     * Route : /manga/lien
     */
    public function lien(): void
    {
        $this->title = 'Manga | Lien';
        $this->render('manga/lien');
    }

    /**
     * Collection paginée.
     * Routes : /manga/collection | /manga/collection/page/{page}
     */
    public function collection(string $page = '1'): void
    {
        $mangaModel = $this->mangaModel();
        $pagination = Functions::pagination();

        if (!ctype_digit($page))
        {
            $this->notFound('Page introuvable');
            return;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1)
        {
            $this->notFound('Page introuvable');
            return;
        }

        $compteur = $mangaModel->countFirstTomesPaginate($pagination);

        if ($compteur > 0 && $currentPage > $compteur)
        {
            $this->notFound('Page introuvable');
            return;
        }

        $mangas = $mangaModel->findAllFirstTomes('id DESC', $pagination, $currentPage);

        $this->title = 'Manga | Collection';

        if ($currentPage > 1)
        {
            $this->title .= ' - Page ' . $currentPage;
        }

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'compteur' => $compteur,
            'slugFilter' => null,
            'currentPage' => $currentPage
        ]);
    }

    /**
     * Collection AJAX.
     * Route : /manga/collection-ajax/page/{page}
     */
    public function collectionAjax(string $page = '1'): void
    {
        $mangaModel = $this->mangaModel();
        $pagination = Functions::pagination();

        if (!ctype_digit($page))
        {
            $this->notFound('Page introuvable');
            return;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1)
        {
            $this->notFound('Page introuvable');
            return;
        }

        $compteur = $mangaModel->countFirstTomesPaginate($pagination);

        if ($compteur > 0 && $currentPage > $compteur)
        {
            $this->notFound('Page introuvable');
            return;
        }

        $mangas = $mangaModel->findAllFirstTomes('id DESC', $pagination, $currentPage);

        $this->renderPartial('manga/partials/collection_ajax', [
            'mangas' => $mangas,
            'compteur' => $compteur,
            'currentPage' => $currentPage,
            'slugFilter' => null
        ]);
    }

    /**
     * Recherche manga.
     * Route : /manga/recherche/{query}
     */
    public function recherche(string $query = ''): void
    {
        $search = trim(str_replace('-', ' ', urldecode($query)));

        $this->title = 'Manga | Recherche';

        if ($search === '')
        {
            $this->render('manga/search', [
                'mangas' => [],
                'search' => ''
            ]);

            return;
        }

        $mangas = $this->mangaModel()->searchMangas($search);

        $this->title = 'Manga | Recherche : ' . $search;

        $this->render('manga/search', [
            'mangas' => $mangas,
            'search' => $search
        ]);
    }

    /**
     * Recherche AJAX (live search).
     * Route : /manga/search-ajax/{query}
     */
    public function searchAjax(string $query = ''): void
    {
        $search = trim(str_replace('-', ' ', urldecode($query)));

        if ($search === '')
        {
            $this->jsonResponse([]);
            return;
        }

        $mangas = $this->mangaModel()->searchMangas($search);
        $results = [];

        foreach (array_slice($mangas, 0, 6) as $manga)
        {
            $results[] = [
                'slug' => $manga->slug,
                'numero' => (int) $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension,
                'note' => $manga->note
            ];
        }

        $this->jsonResponse($results);
        return;
    }

    /**
     * Affiche une série.
     * Route : /manga/serie/{slug}
     */
    public function serie(string $slug): void
    {
        $requestedSlug = trim($slug);
        $normalizedSlug = Functions::normalizeSlug($slug);

        $mangaModel = $this->mangaModel();
        $mangas = $mangaModel->findBySlug($normalizedSlug);

        if (!$mangas)
        {
            $this->notFound('Manga introuvable');
            return;
        }

        $canonicalSlug = Functions::normalizeSlug($mangas[0]->slug);

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $canonicalSlug,
            'manga/serie/'
        );

        $this->title = 'Manga | ' . $mangas[0]->livre;

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'compteur' => null,
            'slugFilter' => $canonicalSlug,
            'currentPage' => 1
        ]);
    }

    /**
     * Affiche un tome.
     * Route : /manga/{slug}/{numero}
     */
    public function show(string $slug, string $numero): void
    {
        $requestedSlug = trim($slug);
        $normalizedSlug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($normalizedSlug, $numero);

        if (!$manga)
        {
            $this->notFound('Manga introuvable');
            return;
        }

        $canonicalSlug = Functions::normalizeSlug($manga->slug);

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $canonicalSlug,
            'manga/',
            (int) $manga->numero
        );

        $this->title = 'Manga | ' . $manga->livre;

        $this->render('manga/livre', [
            'manga' => $manga
        ]);
    }

    /**
     * Formulaire d'ajout.
     * Route : /manga/ajouter
     */
    public function ajouter(): void
    {
        $this->title = 'Manga | Ajouter';
        $this->render('manga/ajouter');
    }

    /**
     * Formulaire de modification.
     * Route : /manga/update/{slug}/{numero}
     */
    public function modifier(string $slug, string $numero): void
    {
        $requestedSlug = trim($slug);
        $normalizedSlug = Functions::normalizeSlug($requestedSlug);
        $numero = (int) $numero;

        $manga = $this->mangaModel()
            ->findOneBySlugAndNumero($normalizedSlug, $numero);

        if (!$manga)
        {
            $this->notFound('Manga introuvable');
            return;
        }

        $canonicalSlug = Functions::normalizeSlug((string) $manga->slug);

        $this->redirectToCanonicalUrl(
            $requestedSlug,
            $canonicalSlug,
            'manga/update/',
            (int) $manga->numero
        );

        $this->title = 'Manga | Modifier';

        $this->render('manga/edit', [
            'manga' => $manga
        ]);
    }


    /**
     * Traite l'ajout.
     * Route : POST /manga/ajouter
     */
    public function ajouterTraitement(): void
    {
        if (!Functions::isPost())
        {
            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée'
                ], 405);
                return;
            }

            $this->methodNotAllowed('Méthode non autorisée pour l’ajout d’un manga');
            return;
        }

        $validator = $this->makeValidator($_POST, $_FILES);

        $validator
            ->required('livre', 'Le titre est obligatoire.')
            ->string('livre', 'Le titre doit être une chaîne.')
            ->maxLength('livre', 150, 'Le titre ne doit pas dépasser 150 caractères.')
            ->required('slug', 'Le slug est obligatoire.')
            ->string('slug', 'Le slug doit être une chaîne.')
            ->maxLength('slug', 150, 'Le slug ne doit pas dépasser 150 caractères.')
            ->required('numero', 'Le numéro est obligatoire.')
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur à 0.')
            ->max('numero', 999, 'Le numéro ne doit pas dépasser 999.')
            ->nullable('commentaire')
            ->string('commentaire', 'Le commentaire doit être un texte.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.')
            ->fileRequired('image', 'Aucune image envoyée.')
            ->fileOk('image', 'Erreur lors de l’envoi du fichier.')
            ->imageExtension('image', Functions::uploadAllowedExtensions(), 'Format image non autorisé.')
            ->imageMime('image', Functions::uploadAllowedMimeTypes(), 'Type MIME image non autorisé.')
            ->maxFileSize('image', Functions::uploadMaxSize(), 'L’image ne doit pas dépasser la taille autorisée.');

        if ($validator->fails())
        {
            if ($this->isAjaxRequest())
            {
                $errors = $validator->errors();
                $firstError = '';

                if (!empty($errors))
                {
                    $firstError = (string) reset($errors);
                }

                $this->jsonResponse([
                    'success' => false,
                    'message' => $firstError !== '' ? $firstError : 'Le formulaire contient des erreurs.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->redirectWithValidationErrors('manga/ajouter', $validator->errors());
            return;
        }

        $mangaModel = $this->mangaModel();

        $livre = Functions::postString('livre');
        $slug = Functions::normalizeSlug(Functions::postString('slug'));
        $numero = Functions::postInt('numero');
        $commentaire = Functions::normalizeCommentaire(
            Functions::postNullableString('commentaire')
        );

        if (!$this->isTestUploadMode() && $mangaModel->findOneBySlugAndNumero($slug, $numero))
        {
            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ce manga existe déjà'
                ], 409);
                return;
            }

            $this->redirectWithError('manga/ajouter', 'Ce manga existe déjà');
            return;
        }

        $upload = $this->uploadThumbnail($livre, $numero);

        if ($this->isTestUploadMode())
        {
            Session::forget(['errors', 'old']);

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Upload test OK (aucune écriture en base)',
                    'file' => basename($upload['destination'])
                ]);
                return;
            }

            $this->redirectWithSuccess(
                'manga/ajouter',
                'Upload test OK (aucune écriture en base)'
            );
            return;
        }

        $insert = $mangaModel->insert([
            'thumbnail' => $upload['thumbnail'],
            'extension' => $upload['extension'],
            'slug' => $slug,
            'livre' => $livre,
            'numero' => $numero,
            'jacquette' => null,
            'livre_note' => null,
            'commentaire' => $commentaire
        ]);

        if (!$insert)
        {
            $this->removeFileIfExists($upload['destination']);

            Logger::error(
                'Insertion manga échouée après upload. slug='
                . $slug
                . ', numero='
                . $numero
            );

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement du manga'
                ], 500);
                return;
            }

            $this->redirectWithError('manga/ajouter', 'Erreur lors de l’enregistrement du manga');
            return;
        }

        Session::forget(['errors', 'old']);

        if ($this->isAjaxRequest())
        {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Manga ajouté avec succès'
            ]);
            return;
        }

        $this->redirectWithSuccess('manga/ajouter', 'Manga ajouté avec succès');
        return;
    }

    /**
     * Valide et déplace l'image uploadée.
     * Retourne les infos utiles pour l'insertion.
     *
     * @return array{
     *     thumbnail: string,
     *     extension: string,
     *     destination: string
     * }
     */
    protected function uploadThumbnail(string $livre, int $numero): array
    {
        $extension = Functions::fileExtension('image');

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        if ($extension === null)
        {
            Logger::error('Upload manga: extension introuvable.');

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Extension image introuvable'
                ], 422);
            }

            $this->redirectWithError('manga/ajouter', 'Extension image introuvable');
        }

        $mimeType = Functions::fileMimeType('image');

        if (
            $mimeType === null
            || !in_array($mimeType, Functions::uploadAllowedMimeTypes(), true)
        )
        {
            Logger::error(
                'Upload manga refusé: type MIME invalide. MIME reçu: '
                . ($mimeType ?? 'null')
            );

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Type MIME image non autorisé'
                ], 422);
            }

            $this->redirectWithError('manga/ajouter', 'Type MIME image non autorisé');
        }

        $tmpName = Functions::fileTmp('image');

        if ($tmpName === null || !is_uploaded_file($tmpName))
        {
            Logger::error('Upload manga: fichier temporaire invalide ou absent.');

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Fichier temporaire introuvable'
                ], 422);
            }

            $this->redirectWithError('manga/ajouter', 'Fichier temporaire introuvable');
        }

        $thumbnail = Functions::buildThumbnailName($livre, $numero);

        if ($thumbnail === '')
        {
            Logger::error('Upload manga: nom de thumbnail invalide.');

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Nom de fichier invalide'
                ], 422);
            }

            $this->redirectWithError('manga/ajouter', 'Nom de fichier invalide');
        }

        $dossier = Functions::mangaThumbnailDirectory();

        if ($this->isTestUploadMode())
        {
            $dossier = $this->testUploadDirectory();
        }

        if (!is_dir($dossier) && !mkdir($dossier, 0777, true) && !is_dir($dossier))
        {
            Logger::error('Upload manga: impossible de créer le dossier image : ' . $dossier);

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Dossier image introuvable'
                ], 500);
            }

            $this->redirectWithError('manga/ajouter', 'Dossier image introuvable');
        }

        if (!is_dir($dossier))
        {
            Logger::error('Upload manga: dossier image introuvable : ' . $dossier);

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Dossier image introuvable'
                ], 500);
            }

            $this->redirectWithError('manga/ajouter', 'Dossier image introuvable');
        }

        $destination = $dossier . $thumbnail . '.' . $extension;

        if (file_exists($destination))
        {
            Logger::error('Upload manga: fichier déjà existant : ' . $destination);

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Une image avec ce nom existe déjà'
                ], 409);
            }

            $this->redirectWithError('manga/ajouter', 'Une image avec ce nom existe déjà');
        }

        if (!move_uploaded_file($tmpName, $destination))
        {
            Logger::error('Upload manga: échec move_uploaded_file vers : ' . $destination);

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de l’upload de l’image'
                ], 500);
            }

            $this->redirectWithError('manga/ajouter', 'Erreur lors de l’upload de l’image');
        }

        return [
            'thumbnail' => $thumbnail,
            'extension' => $extension,
            'destination' => $destination
        ];
    }

    /**
     * Supprime un fichier si présent.
     */
    protected function removeFileIfExists(string $path): void
    {
        if (is_file($path))
        {
            unlink($path);
        }
    }

    /**
     * Traite la modification.
     * Route : POST /manga/update/{slug}/{numero}
     */
    public function update(string $slug, string $numero): void
    {
        $requestedSlug = trim($slug);
        $normalizedSlug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        if (!Functions::isPost())
        {
            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Méthode non autorisée'
                ], 405);
                return;
            }

            $this->methodNotAllowed('Méthode non autorisée pour la modification d’un manga');
            return;
        }

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($normalizedSlug, $numero);

        if (!$manga)
        {
            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Manga introuvable'
                ], 404);
                return;
            }

            $this->notFound('Manga introuvable');
            return;
        }

        $canonicalSlug = Functions::normalizeSlug($manga->slug);

        if ($requestedSlug !== $canonicalSlug)
        {
            $redirect = Functions::basePath() . 'manga/update/' . rawurlencode($canonicalSlug) . '/' . $numero;

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'URL non canonique',
                    'redirect' => $redirect
                ], 409);
                return;
            }

            header('Location: ' . $redirect, true, 301);
            exit;
        }

        $slug = $canonicalSlug;

        $validator = $this->makeValidator($_POST, $_FILES);

        $validator
            ->nullable('commentaire')
            ->string('commentaire', 'Le commentaire doit être un texte.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.')
            ->nullable('jacquette')
            ->integer('jacquette', 'La note jacquette doit être un entier.')
            ->min('jacquette', 1, 'La note jacquette doit être supérieure ou égale à 1.')
            ->max('jacquette', 5, 'La note jacquette doit être inférieure ou égale à 5.')
            ->nullable('livre_note')
            ->integer('livre_note', 'La note du livre doit être un entier.')
            ->min('livre_note', 1, 'La note du livre doit être supérieure ou égale à 1.')
            ->max('livre_note', 5, 'La note du livre doit être inférieure ou égale à 5.');

        if ($validator->fails())
        {
            if ($this->isAjaxRequest())
            {
                $errors = $validator->errors();
                $firstError = '';

                if (!empty($errors))
                {
                    $firstError = (string) reset($errors);
                }

                $this->jsonResponse([
                    'success' => false,
                    'message' => $firstError !== '' ? $firstError : 'Le formulaire contient des erreurs.',
                    'errors' => $errors
                ], 422);
                return;
            }

            $this->redirectWithValidationErrors(
                'manga/update/' . rawurlencode($slug) . '/' . $numero,
                $validator->errors()
            );
            return;
        }

        $jacquette = $this->normalizePostedNote(
            Functions::postNullableString('jacquette')
        );

        $livreNote = $this->normalizePostedNote(
            Functions::postNullableString('livre_note')
        );

        $commentaire = Functions::normalizeCommentaire(
            Functions::postNullableString('commentaire')
        );

        $update = $mangaModel->updateManga(
            $slug,
            $numero,
            $jacquette,
            $livreNote,
            $commentaire
        );

        if (!$update)
        {
            Logger::error(
                'Échec update manga. slug='
                . $slug
                . ', numero='
                . $numero
            );

            if ($this->isAjaxRequest())
            {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ], 500);
                return;
            }

            $this->redirectWithError(
                'manga/update/' . rawurlencode($slug) . '/' . $numero,
                'Erreur lors de la mise à jour'
            );
            return;
        }

        Session::forget(['errors', 'old']);

        if ($this->isAjaxRequest())
        {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Manga mis à jour avec succès',
                'redirect' => Functions::basePath() . '/manga/' . rawurlencode($slug) . '/' . $numero
            ]);
            return;
        }

        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            'Manga mis à jour avec succès'
        );
        return;
    }

    /**
     * Mise à jour AJAX des notes.
     * Route : POST /manga/ajax/update-note/{slug}/{numero}
     */
    public function ajaxUpdateNote(string $slug, string $numero): void
    {
        $requestedSlug = trim($slug);
        $normalizedSlug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        if (!Functions::isPost())
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ], 405);
            return;
        }

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($normalizedSlug, $numero);

        if (!$manga)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Manga introuvable'
            ], 404);
            return;
        }

        $canonicalSlug = Functions::normalizeSlug($manga->slug);

        if ($requestedSlug !== $canonicalSlug)
        {
            $this->jsonResponse([
                'success' => false,
                'message' => 'URL non canonique',
                'redirect' => Functions::basePath() . '/manga/' . rawurlencode($canonicalSlug) . '/' . $numero
            ], 409);
            return;
        }

        $slug = $canonicalSlug;

        $validator = $this->makeValidator($_POST, $_FILES);

        $validator
            ->nullable('commentaire')
            ->string('commentaire', 'Le commentaire doit être un texte.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.')
            ->nullable('jacquette')
            ->integer('jacquette', 'La note jacquette doit être un entier.')
            ->min('jacquette', 1, 'La note jacquette doit être supérieure ou égale à 1.')
            ->max('jacquette', 5, 'La note jacquette doit être inférieure ou égale à 5.')
            ->nullable('livre_note')
            ->integer('livre_note', 'La note du livre doit être un entier.')
            ->min('livre_note', 1, 'La note du livre doit être supérieure ou égale à 1.')
            ->max('livre_note', 5, 'La note du livre doit être inférieure ou égale à 5.');

        if ($validator->fails())
        {
            $errors = $validator->errors();
            $firstError = '';

            if (!empty($errors))
            {
                $firstError = (string) reset($errors);
            }

            $this->jsonResponse([
                'success' => false,
                'message' => $firstError !== '' ? $firstError : 'Le formulaire contient des erreurs.',
                'errors' => $errors
            ], 422);
            return;
        }

        $jacquette = $this->normalizePostedNote(
            Functions::postNullableString('jacquette')
        );

        $livreNote = $this->normalizePostedNote(
            Functions::postNullableString('livre_note')
        );

        $commentaire = Functions::normalizeCommentaire(
            Functions::postNullableString('commentaire')
        );

        $updated = $mangaModel->updateManga(
            $slug,
            $numero,
            $jacquette,
            $livreNote,
            $commentaire
        );

        if (!$updated)
        {
            Logger::error(
                'Échec update AJAX manga. slug='
                . $slug
                . ', numero='
                . $numero
            );

            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
            return;
        }

        $fresh = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Notes mises à jour',
            'jacquette' => $fresh->jacquette,
            'livre_note' => $fresh->livre_note,
            'note' => $fresh->note
        ]);
        return;
    }
}