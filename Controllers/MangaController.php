<?php

namespace App\Controllers;

use App\Core\Functions;
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
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
            ->imageExtension('image', ['jpg', 'png', 'webp'], 'Format image non autorisé.')
            ->maxFileSize('image', 5 * 1024 * 1024, 'L’image ne doit pas dépasser 5 Mo.');

        if ($validator->fails())
        {
            $this->redirectWithValidationErrors('manga/ajouter', $validator->errors());
        }

        $mangaModel = $this->mangaModel();
        $livre = Functions::postString('livre');
        $slug = Functions::normalizeSlug(Functions::postString('slug'));
        $numero = Functions::postInt('numero');
        $commentaire = Functions::postNullableString('commentaire');

        if ($mangaModel->findOneBySlugAndNumero($slug, $numero))
        {
            $this->redirectWithError('manga/ajouter', 'Ce manga existe déjà');
        }

        $thumbnail = preg_replace('/[^A-Za-z0-9\- ]/', '', strtoupper($livre));
        $thumbnail = preg_replace('/\s+/', ' ', trim($thumbnail));
        $thumbnail .= ' ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);

        if ($thumbnail === '')
        {
            $this->redirectWithError('manga/ajouter', 'Nom de fichier invalide');
        }

        $extension = Functions::fileExtension('image');

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        if ($extension === null)
        {
            $this->redirectWithError('manga/ajouter', 'Extension image introuvable');
        }

        $nomFichier = $thumbnail . '.' . $extension;
        $dossier = ROOT . '/public/images/mangas/thumbnail/';
        $destination = $dossier . $nomFichier;
        $tmpName = Functions::fileTmp('image');

        if (!is_dir($dossier))
        {
            $this->redirectWithError('manga/ajouter', 'Dossier image introuvable');
        }

        if ($tmpName === null)
        {
            $this->redirectWithError('manga/ajouter', 'Fichier temporaire introuvable');
        }

        if (file_exists($destination))
        {
            $this->redirectWithError('manga/ajouter', 'Une image avec ce nom existe déjà');
        }

        if (!move_uploaded_file($tmpName, $destination))
        {
            $this->redirectWithError('manga/ajouter', 'Erreur lors de l’upload de l’image');
        }

        $insert = $mangaModel->insert([
            'thumbnail' => $thumbnail,
            'extension' => $extension,
            'slug' => $slug,
            'livre' => $livre,
            'numero' => $numero,
            'jacquette' => null,
            'livre_note' => null,
            'commentaire' => $commentaire
        ]);

        if (!$insert)
        {
            if (file_exists($destination))
            {
                unlink($destination);
            }

            $this->redirectWithError('manga/ajouter', 'Erreur lors de l’enregistrement du manga');
        }

        Session::forget(['errors', 'old']);
        $this->redirectWithSuccess('manga/ajouter', 'Manga ajouté avec succès');
    }

    /**
     * Traite la modification.
     * Route : POST /manga/update/{slug}/{numero}
     */
    public function update(string $slug, string $numero): void
    {
        $slug = Functions::normalizeSlug($slug);
        $numero = (int) $numero;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
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
            $this->redirectWithValidationErrors('manga/update/' . rawurlencode($slug) . '/' . $numero, $validator->errors());
        }

        $jacquette = $this->normalizePostedNote($_POST['jacquette'] ?? null);
        $livreNote = $this->normalizePostedNote($_POST['livre_note'] ?? null);
        $commentaire = Functions::postNullableString('commentaire');

        $update = $mangaModel->updateManga($slug, $numero, $jacquette, $livreNote, $commentaire);

        if (!$update)
        {
            $this->redirectWithError('manga/update/' . rawurlencode($slug) . '/' . $numero, 'Erreur lors de la mise à jour');
        }

        Session::forget(['errors', 'old']);
        $this->redirectWithSuccess('manga/' . rawurlencode($slug) . '/' . $numero, 'Manga mis à jour avec succès');
    }
}