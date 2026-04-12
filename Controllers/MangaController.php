<?php
namespace App\Controllers;

use App\Models\MangaModel;

class MangaController extends Controller
{
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
     * 1. sans slug => collection générale
     * 2. slug + numero => détail tome
     * 3. slug seul => collection d'un manga
     */
    public function collection(?string $slug = null, ?string $numero = null): void
    {
        $mangaModel = new MangaModel;

        /* collection générale */
        if ($slug === null || $slug === '')
        {
            $page = 1;

            $mangas = $mangaModel->findAllFirstTomes('id DESC', 8, $page);
            $compteur = $mangaModel->countFirstTomesPaginate(8);

            $this->render('manga/collection', [
                'mangas' => $mangas,
                'compteur' => $compteur,
                'titleFilter' => null
            ]);

            return;
        }

        /* page détail */
        if ($numero !== null && $numero !== '')
        {
            $numero = (int) $numero;
            $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

            if (!$manga)
            {
                http_response_code(404);
                exit('Manga introuvable');
            }

            /* slug propre depuis la db */
            $goodSlug = strtolower(str_replace(' ', '-', trim($manga->livre)));

            if ($goodSlug !== strtolower(trim($slug)))
            {
                header('Location: ' . $this->basePath . 'manga/collection/' . $goodSlug . '/' . $manga->numero);
                exit;
            }

            $this->render('manga/livre', [
                'manga' => $manga
            ]);

            return;
        }

        /* collection d'un manga */
        $mangas = $mangaModel->findBySlug($slug);

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'titleFilter' => $slug
        ]);
    }

    /**
     * /manga/page/{id}
     * pagination collection générale
     */
    public function page(string $id): void
    {
        $id = (int) $id;

        if ($id < 1)
        {
            $id = 1;
        }

        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllFirstTomes('id DESC', 8, $id);
        $compteur = $mangaModel->countFirstTomesPaginate(8);

        $this->title = 'Manga | Collection Page ' . $id;

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'compteur' => $compteur,
            'titleFilter' => null
        ]);
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
     * affiche le formulaire
     */
    public function ajouter(): void
    {
        $this->title = 'Manga | Ajouter';
        $this->render('manga/ajouter');
    }

    /**
     * traitement ajout manga
     */
    public function ajouterTraitement(): void
    {
        /* refuse accès direct */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/ajouter');
            exit;
        }

        /* récup données */
        $livre = trim($_POST['livre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $numero = (int) ($_POST['numero'] ?? 0);

        /* validation form */
        if (
            $livre === ''
            || $slug === ''
            || $numero <= 0
            || empty($_FILES['image']['name'])
        )
        {
            exit('Formulaire incomplet');
        }

        /* vérif upload */
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK)
        {
            exit('Erreur fichier');
        }

        /* extension */
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if ($extension === 'jpeg')
        {
            $extension = 'jpg';
        }

        $extensionsAutorisees = ['jpg', 'png', 'webp'];

        if (!in_array($extension, $extensionsAutorisees, true))
        {
            exit('Format image non autorisé');
        }

        /* nom du thumbnail */
        $thumbnail = preg_replace('/[^A-Za-z0-9\- ]/', '', strtoupper($livre));
        $thumbnail .= ' ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
        $thumbnail = preg_replace('/\s+/', ' ', $thumbnail);
        $thumbnail = trim($thumbnail);

        $nomFichier = $thumbnail . '.' . $extension;

        /* dossier image */
        $dossier = dirname(__DIR__) . '/public/images/mangas/thumbnail/';

        if (!is_dir($dossier))
        {
            exit('Dossier image introuvable : ' . $dossier);
        }

        $destination = $dossier . $nomFichier;

        /* évite doublon */
        if (file_exists($destination))
        {
            exit('Une image avec ce nom existe déjà');
        }

        /* déplacement image */
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination))
        {
            exit('Erreur lors de l\'upload');
        }

        /* insert db */
        $mangaModel = new MangaModel;

        $mangaModel->insert([
            'thumbnail' => $thumbnail,
            'extension' => $extension,
            'slug' => strtolower(trim($slug)),
            'livre' => $livre,
            'numero' => $numero,
            'jacquette' => null,
            'livre_note' => null,
            'note' => null
        ]);

        $_SESSION['success'] = 'Manga ajouté avec succès';

        header('Location: ' . $this->basePath . 'manga/ajouter');
        exit;
    }

    /**
     * /manga/edit/{slug}/{numero}
     * page édition
     */
    public function edit(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        $mangaModel = new MangaModel;
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga)
        {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $this->render('manga/edit', [
            'manga' => $manga
        ]);
    }

    /**
     * update jacquette + livre_note
     * note = calcul automatique
     */
    public function update(string $slug, string $numero): void
    {
        $numero = (int) $numero;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            header('Location: ' . $this->basePath . 'manga/collection/' . $slug . '/' . $numero);
            exit;
        }

        $jacquette = $_POST['jacquette'] ?? null;
        $livreNote = $_POST['livre_note'] ?? null;

        if ($jacquette === '' || $livreNote === '')
        {
            exit('Formulaire incomplet');
        }

        $jacquette = (int) $jacquette;
        $livreNote = (int) $livreNote;

        if ($jacquette < 1 || $jacquette > 5 || $livreNote < 1 || $livreNote > 5)
        {
            exit('Note invalide');
        }

        $mangaModel = new MangaModel;
        $mangaModel->updateNotes($slug, $numero, $jacquette, $livreNote);

        header('Location: ' . $this->basePath . 'manga/collection/' . $slug . '/' . $numero);
        exit;
    }
}