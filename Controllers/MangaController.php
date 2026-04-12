<?php

namespace App\Controllers;

use App\Core\Functions;
use App\Models\MangaModel;

class MangaController extends Controller
{
    /**
     * retourne une instance du model manga
     */
    private function mangaModel(): MangaModel
    {
        return new MangaModel();
    }

    /**
     * normalise un slug
     */
    private function normalizeSlug(string $slug): string
    {
        return strtolower(trim($slug));
    }

    /**
     * convertit une note postée en int ou null
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
     * nettoie un commentaire
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
     * accueil manga
     */
    public function index(): void
    {
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * /manga/collection
     *
     * 1. sans paramètre
     *    → collection générale, page 1
     *
     * 2. page/{numero}
     *    → pagination de la collection générale
     *
     * 3. {slug}/{numero}
     *    → détail d'un tome précis
     *
     * 4. {slug}
     *    → collection d'un manga
     */
    public function collection(?string $slug = null, ?string $numero = null): void
    {
        $mangaModel = $this->mangaModel();
        $pagination = Functions::pagination();

        /**
         * cas 1 :
         * /manga/collection
         */
        if ($slug === null || trim($slug) === '')
        {
            $page = 1;

            $mangas = $mangaModel->findAllFirstTomes('id DESC', $pagination, $page);
            $compteur = $mangaModel->countFirstTomesPaginate($pagination);

            $this->title = 'Manga | Collection';

            $this->render('manga/collection', [
                'mangas' => $mangas,
                'compteur' => $compteur,
                'slugFilter' => null,
                'currentPage' => $page
            ]);
            return;
        }

        /**
         * cas 2 :
         * /manga/collection/page/{numero}
         */
        if ($slug === 'page')
        {
            $page = (int) ($numero ?? 1);

            if ($page < 1)
            {
                $page = 1;
            }

            $mangas = $mangaModel->findAllFirstTomes('id DESC', $pagination, $page);
            $compteur = $mangaModel->countFirstTomesPaginate($pagination);

            $this->title = 'Manga | Collection - Page ' . $page;

            $this->render('manga/collection', [
                'mangas' => $mangas,
                'compteur' => $compteur,
                'slugFilter' => null,
                'currentPage' => $page
            ]);
            return;
        }

        /**
         * cas 3 :
         * /manga/collection/{slug}/{numero}
         */
        if ($numero !== null && trim($numero) !== '')
        {
            $slug = $this->normalizeSlug($slug);
            $numero = (int) $numero;

            $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

            if (!$manga)
            {
                http_response_code(404);
                exit('Manga introuvable');
            }

            $goodSlug = $this->normalizeSlug($manga->slug);

            if ($goodSlug !== $slug)
            {
                header('Location: ' . $this->basePath . 'manga/collection/' . $goodSlug . '/' . $manga->numero);
                exit;
            }

            $this->title = 'Manga | ' . $manga->livre . ' ' . str_pad((string) $manga->numero, 2, '0', STR_PAD_LEFT);

            $this->render('manga/livre', [
                'manga' => $manga
            ]);
            return;
        }

        /**
         * cas 4 :
         * /manga/collection/{slug}
         */
        $slug = $this->normalizeSlug($slug);
        $mangas = $mangaModel->findBySlug($slug);

        if (!$mangas)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $this->title = 'Manga | ' . $mangas[0]->livre;

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'slugFilter' => $slug
        ]);
    }

    /**
     * /manga/ajouter
     * affiche le formulaire d'ajout
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
     * /manga/ajouterTraitement
     * traite l'ajout d'un manga
     */
    public function ajouterTraitement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $mangaModel = $this->mangaModel();

        $livre = trim($_POST['livre'] ?? '');
        $slug = $this->normalizeSlug($_POST['slug'] ?? '');
        $numero = (int) ($_POST['numero'] ?? 0);
        $commentaire = $this->normalizeCommentaire($_POST['commentaire'] ?? null);

        if ($livre === '' || $slug === '' || $numero <= 0)
        {
            $_SESSION['error'] = 'Formulaire incomplet';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if (!isset($_FILES['image']) || empty($_FILES['image']['name']))
        {
            $_SESSION['error'] = 'Aucune image envoyée';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK)
        {
            $_SESSION['error'] = 'Erreur lors de l’envoi du fichier';
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
            $_SESSION['error'] = 'Format image non autorisé';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if ($mangaModel->findOneBySlugAndNumero($slug, $numero))
        {
            $_SESSION['error'] = 'Ce manga existe déjà';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $thumbnail = preg_replace('/[^A-Za-z0-9\- ]/', '', strtoupper($livre));
        $thumbnail = preg_replace('/\s+/', ' ', trim($thumbnail));
        $thumbnail .= ' ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);

        if ($thumbnail === '')
        {
            $_SESSION['error'] = 'Nom de fichier invalide';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $nomFichier = $thumbnail . '.' . $extension;
        $dossier = ROOT . '/public/images/mangas/thumbnail/';

        if (!is_dir($dossier))
        {
            $_SESSION['error'] = 'Dossier image introuvable';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $destination = $dossier . $nomFichier;

        if (file_exists($destination))
        {
            $_SESSION['error'] = 'Une image avec ce nom existe déjà';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination))
        {
            $_SESSION['error'] = 'Erreur lors de l’upload de l’image';
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

            $_SESSION['error'] = 'Erreur lors de l’enregistrement du manga';
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        $_SESSION['success'] = 'Manga ajouté avec succès';
        header('Location: ' . $this->basePath . 'manga/ajouter');
        exit;
    }

    /**
     * /manga/edit/{slug}/{numero}
     * affiche la page d'édition d'un tome
     */
    public function edit(string $slug, string $numero): void
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
            header('Location: ' . $this->basePath . 'manga/edit/' . $goodSlug . '/' . $manga->numero);
            exit;
        }

        $this->title = 'Manga | Modifier';
        $this->render('manga/edit', [
            'manga' => $manga
        ]);
    }

    /**
     * /manga/update/{slug}/{numero}
     * met à jour jacquette, livre_note, note et commentaire
     */
    public function update(string $slug, string $numero): void
    {
        $slug = $this->normalizeSlug($slug);
        $numero = (int) $numero;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/collection/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        $mangaModel = $this->mangaModel();
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $jacquetteRaw = $_POST['jacquette'] ?? null;
        $livreNoteRaw = $_POST['livre_note'] ?? null;

        $jacquette = $this->normalizePostedNote($jacquetteRaw);
        $livreNote = $this->normalizePostedNote($livreNoteRaw);
        $commentaire = $this->normalizeCommentaire($_POST['commentaire'] ?? null);

        if (($jacquetteRaw !== null && trim((string) $jacquetteRaw) !== '' && $jacquette === null) ||
            ($livreNoteRaw !== null && trim((string) $livreNoteRaw) !== '' && $livreNote === null))
        {
            $_SESSION['error'] = 'Les notes doivent être comprises entre 1 et 5';
            header('Location: ' . $this->basePath . 'manga/edit/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        $update = $mangaModel->updateManga(
            $slug,
            $numero,
            $jacquette,
            $livreNote,
            $commentaire
        );

        if (!$update)
        {
            $_SESSION['error'] = 'Erreur lors de la mise à jour';
            header('Location: ' . $this->basePath . 'manga/edit/' . rawurlencode($slug) . '/' . $numero);
            exit;
        }

        $_SESSION['success'] = 'Manga mis à jour avec succès';
        header('Location: ' . $this->basePath . 'manga/collection/' . rawurlencode($slug) . '/' . $numero);
        exit;
    }
}