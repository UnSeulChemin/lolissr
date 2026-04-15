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
    private function mangaModel(): MangaModel
    {
        return new MangaModel();
    }

    /**
     * Convertit une note postée.
     * Retourne null si vide ou invalide.
     */
    private function normalizePostedNote(?string $value): ?int
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
        $currentPage = max(1, (int) $page);

        $mangas = $mangaModel->findAllFirstTomes('id DESC', $pagination, $currentPage);
        $compteur = $mangaModel->countFirstTomesPaginate($pagination);

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
            echo json_encode([]);
            return;
        }

        $mangas = $this->mangaModel()->searchMangas($search);

        $results = [];

        foreach (array_slice($mangas, 0, 6) as $manga)
        {
            $results[] = [
                'slug' => $manga->slug,
                'numero' => $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($results);
    }

    /**
     * Affiche une série.
     * Route : /manga/serie/{slug}
     */
    public function serie(string $slug): void
    {
        $slug = Functions::normalizeSlug($slug);
        $mangaModel = $this->mangaModel();
        $mangas = $mangaModel->findBySlug($slug);

        if (!$mangas)
        {
            $this->notFound('Manga introuvable');
        }

        $this->title = 'Manga | ' . $mangas[0]->livre;

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'slugFilter' => $slug,
            'currentPage' => 1,
            'compteur' => 1
        ]);
    }

    /**
     * Affiche un tome.
     * Route : /manga/{slug}/{numero}
     */
    public function show(string $slug, string $numero): void
    {
        $slug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            $this->notFound('Manga introuvable');
        }

        $goodSlug = Functions::normalizeSlug($manga->slug);

        if ($goodSlug !== $slug)
        {
            $this->redirect('manga/' . rawurlencode($goodSlug) . '/' . $manga->numero);
        }

        $this->title = 'Manga | ' . $manga->livre . ' ' . str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT);
        $this->render('manga/livre', ['manga' => $manga]);
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
        $slug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            $this->notFound('Manga introuvable');
        }

        $goodSlug = Functions::normalizeSlug($manga->slug);

        if ($goodSlug !== $slug)
        {
            $this->redirect('manga/update/' . rawurlencode($goodSlug) . '/' . $manga->numero);
        }

        $this->title = 'Manga | Modifier';
        $this->render('manga/edit', ['manga' => $manga]);
    }

    /**
     * Traite l'ajout.
     * Route : POST /manga/ajouter
     */
    public function ajouterTraitement(): void
    {
        if (!Functions::isPost())
        {
            $this->methodNotAllowed('Méthode non autorisée pour l’ajout d’un manga');
        }

        $validator = new Validator($_POST, $_FILES);

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
            $this->redirectWithValidationErrors('manga/ajouter', $validator->errors());
        }

        $mangaModel = $this->mangaModel();

        $livre = Functions::postString('livre');
        $slug = Functions::normalizeSlug(Functions::postString('slug'));
        $numero = Functions::postInt('numero');
        $commentaire = Functions::normalizeCommentaire(
            Functions::postNullableString('commentaire')
        );

        if ($mangaModel->findOneBySlugAndNumero($slug, $numero))
        {
            $this->redirectWithError('manga/ajouter', 'Ce manga existe déjà');
        }

        $upload = $this->uploadThumbnail($livre, $numero);

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

            $this->redirectWithError('manga/ajouter', 'Erreur lors de l’enregistrement du manga');
        }

        Session::forget(['errors', 'old']);
        $this->redirectWithSuccess('manga/ajouter', 'Manga ajouté avec succès');
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
    private function uploadThumbnail(string $livre, int $numero): array
    {
        $extension = Functions::fileExtension('image');

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        if ($extension === null)
        {
            Logger::error('Upload manga: extension introuvable.');
            $this->redirectWithError('manga/ajouter', 'Extension image introuvable');
        }

        $mimeType = Functions::fileMimeType('image');

        if (
            $mimeType === null
            || !in_array($mimeType, Functions::uploadAllowedMimeTypes(), true)
        ) {
            Logger::error(
                'Upload manga refusé: type MIME invalide. MIME reçu: '
                . ($mimeType ?? 'null')
            );

            $this->redirectWithError('manga/ajouter', 'Type MIME image non autorisé');
        }

        $tmpName = Functions::fileTmp('image');

        if ($tmpName === null || !is_uploaded_file($tmpName))
        {
            Logger::error('Upload manga: fichier temporaire invalide ou absent.');
            $this->redirectWithError('manga/ajouter', 'Fichier temporaire introuvable');
        }

        $thumbnail = Functions::buildThumbnailName($livre, $numero);

        if ($thumbnail === '')
        {
            Logger::error('Upload manga: nom de thumbnail invalide.');
            $this->redirectWithError('manga/ajouter', 'Nom de fichier invalide');
        }

        $dossier = Functions::mangaThumbnailDirectory();

        if (!is_dir($dossier))
        {
            Logger::error('Upload manga: dossier image introuvable : ' . $dossier);
            $this->redirectWithError('manga/ajouter', 'Dossier image introuvable');
        }

        $destination = $dossier . $thumbnail . '.' . $extension;

        if (file_exists($destination))
        {
            Logger::error('Upload manga: fichier déjà existant : ' . $destination);
            $this->redirectWithError('manga/ajouter', 'Une image avec ce nom existe déjà');
        }

        if (!move_uploaded_file($tmpName, $destination))
        {
            Logger::error('Upload manga: échec move_uploaded_file vers : ' . $destination);
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
    private function removeFileIfExists(string $path): void
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
        $slug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        if (!Functions::isPost())
        {
            $this->methodNotAllowed('Méthode non autorisée pour la modification d’un manga');
        }

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            $this->notFound('Manga introuvable');
        }

        $validator = new Validator($_POST, $_FILES);

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
            $this->redirectWithValidationErrors(
                'manga/update/' . rawurlencode($slug) . '/' . $numero,
                $validator->errors()
            );
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

            $this->redirectWithError(
                'manga/update/' . rawurlencode($slug) . '/' . $numero,
                'Erreur lors de la mise à jour'
            );
        }

        Session::forget(['errors', 'old']);
        $this->redirectWithSuccess(
            'manga/' . rawurlencode($slug) . '/' . $numero,
            'Manga mis à jour avec succès'
        );
    }
}