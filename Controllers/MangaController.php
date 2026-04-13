<?php

namespace App\Controllers;

use App\Core\Functions;
use App\Core\Session;
use App\Core\Validator;
use App\Models\MangaModel;

class MangaController extends Controller
{
    /**
     * Retourne une instance du model manga.
     */
    private function mangaModel(): MangaModel
    {
        return new MangaModel();
    }

    /**
     * Normalise un slug.
     */
    private function normalizeSlug(string $slug): string
    {
        return strtolower(trim($slug));
    }

    /**
     * Convertit une note postée en int ou null.
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
     * Nettoie un commentaire.
     */
    private function normalizeCommentaire(?string $commentaire): ?string
    {
        if ($commentaire === null)
        {
            return null;
        }

        $commentaire = trim($commentaire);

        return $commentaire === '' ? null : $commentaire;
    }

    /**
     * /manga
     * Accueil manga.
     */
    public function index(): void
    {
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * /manga/collection
     * /manga/collection/page/{page}
     * Affiche la collection générale paginée.
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
     * /manga/serie/{slug}
     * Affiche tous les tomes d'une série.
     */
    public function serie(string $slug): void
    {
        $slug = $this->normalizeSlug($slug);

        $mangaModel = $this->mangaModel();
        $mangas = $mangaModel->findBySlug($slug);

        if (!$mangas)
        {
            http_response_code(404);
            exit('Manga introuvable');
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
     * /manga/{slug}/{numero}
     * Affiche le détail d'un tome.
     */
    public function show(string $slug, string $numero): void
    {
        $slug = $this->normalizeSlug($slug);
        $numero = (int) $numero;

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $goodSlug = $this->normalizeSlug($manga->slug);

        if ($goodSlug !== $slug)
        {
            header('Location: ' . $this->basePath . 'manga/' . rawurlencode($goodSlug) . '/' . $manga->numero);
            exit;
        }

        $this->title = 'Manga | ' . $manga->livre . ' ' . str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT);

        $this->render('manga/livre', [
            'manga' => $manga
        ]);
    }

    /**
     * /manga/ajouter
     * Affiche le formulaire d'ajout.
     */
    public function ajouter(): void
    {
        $this->title = 'Manga | Ajouter';
        $this->render('manga/ajouter');
    }

    /**
     * /manga/lien
     */
    public function lien(): void
    {
        $this->title = 'Manga | Lien';
        $this->render('manga/lien');
    }

    /**
     * /manga/ajouter
     * Traite l'ajout d'un manga.
     */
    public function ajouterTraitement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $validator = new Validator($_POST);

        $validator
            ->required('livre', 'Le titre est obligatoire.')
            ->maxLength('livre', 150, 'Le titre ne doit pas dépasser 150 caractères.')
            ->required('slug', 'Le slug est obligatoire.')
            ->maxLength('slug', 150, 'Le slug ne doit pas dépasser 150 caractères.')
            ->required('numero', 'Le numéro est obligatoire.')
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur à 0.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.');

        if ($validator->fails())
        {
            Session::set('errors', $validator->errors());
            Session::set('old', $_POST);
            Session::set('error', 'Le formulaire contient des erreurs.');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $mangaModel = $this->mangaModel();

        $livre = trim($_POST['livre'] ?? '');
        $slug = $this->normalizeSlug($_POST['slug'] ?? '');
        $numero = (int) ($_POST['numero'] ?? 0);
        $commentaire = $this->normalizeCommentaire($_POST['commentaire'] ?? null);

        if (!isset($_FILES['image']) || empty($_FILES['image']['name']))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Aucune image envoyée');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK)
        {
            Session::set('old', $_POST);
            Session::set('error', 'Erreur lors de l’envoi du fichier');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        $extensionsAutorisees = ['jpg', 'png', 'webp'];

        if (!in_array($extension, $extensionsAutorisees, true))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Format image non autorisé');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if ($mangaModel->findOneBySlugAndNumero($slug, $numero))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Ce manga existe déjà');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $thumbnail = preg_replace('/[^A-Za-z0-9\- ]/', '', strtoupper($livre));
        $thumbnail = preg_replace('/\s+/', ' ', trim($thumbnail));
        $thumbnail .= ' ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);

        if ($thumbnail === '')
        {
            Session::set('old', $_POST);
            Session::set('error', 'Nom de fichier invalide');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $nomFichier = $thumbnail . '.' . $extension;
        $dossier = ROOT . '/public/images/mangas/thumbnail/';

        if (!is_dir($dossier))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Dossier image introuvable');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $destination = $dossier . $nomFichier;

        if (file_exists($destination))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Une image avec ce nom existe déjà');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination))
        {
            Session::set('old', $_POST);
            Session::set('error', 'Erreur lors de l’upload de l’image');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
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

            Session::set('old', $_POST);
            Session::set('error', 'Erreur lors de l’enregistrement du manga');

            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        Session::forget(['errors', 'old']);
        Session::set('success', 'Manga ajouté avec succès');

        header('Location: ' . $this->basePath . 'manga/ajouter');
        exit;
    }

    /**
     * /manga/update/{slug}/{numero}
     * Affiche la page d'édition d'un tome.
     */
    public function modifier(string $slug, string $numero): void
    {
        $slug = $this->normalizeSlug($slug);
        $numero = (int) $numero;

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $goodSlug = $this->normalizeSlug($manga->slug);

        if ($goodSlug !== $slug)
        {
            header('Location: ' . $this->basePath . 'manga/update/' . rawurlencode($goodSlug) . '/' . $manga->numero);
            exit;
        }

        $this->title = 'Manga | Modifier';
        $this->render('manga/edit', [
            'manga' => $manga
        ]);
    }

    /**
     * /manga/update/{slug}/{numero}
     * Met à jour jacquette, livre_note, note et commentaire.
     */
    public function update(string $slug, string $numero): void
    {
        $slug = $this->normalizeSlug($slug);
        $numero = (int) $numero;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $validator = new Validator($_POST);

        $validator
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.');

        $jacquetteRaw = $_POST['jacquette'] ?? null;
        $livreNoteRaw = $_POST['livre_note'] ?? null;

        if ($jacquetteRaw !== null && trim((string) $jacquetteRaw) !== '')
        {
            $validator->in(
                'jacquette',
                ['1', '2', '3', '4', '5'],
                'La note jacquette doit être comprise entre 1 et 5.'
            );
        }

        if ($livreNoteRaw !== null && trim((string) $livreNoteRaw) !== '')
        {
            $validator->in(
                'livre_note',
                ['1', '2', '3', '4', '5'],
                'La note du livre doit être comprise entre 1 et 5.'
            );
        }

        if ($validator->fails())
        {
            Session::set('errors', $validator->errors());
            Session::set('old', $_POST);
            Session::set('error', 'Le formulaire contient des erreurs.');

            header('Location: ' . $this->basePath . 'manga/update/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        $jacquette = $this->normalizePostedNote($jacquetteRaw);
        $livreNote = $this->normalizePostedNote($livreNoteRaw);
        $commentaire = $this->normalizeCommentaire($_POST['commentaire'] ?? null);

        $update = $mangaModel->updateManga(
            $slug,
            $numero,
            $jacquette,
            $livreNote,
            $commentaire
        );

        if (!$update)
        {
            Session::set('old', $_POST);
            Session::set('error', 'Erreur lors de la mise à jour');

            header('Location: ' . $this->basePath . 'manga/update/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        Session::forget(['errors', 'old']);
        Session::set('success', 'Manga mis à jour avec succès');

        header('Location: ' . $this->basePath . 'manga/' . rawurlencode($slug) . '/' . $numero);
        exit;
    }
}