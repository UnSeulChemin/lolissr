<?php
namespace App\Controllers;

use App\Models\MangaModel;

class MangaController extends Controller
{
    /**
     * route /manga
     * @return void
     */
    public function index(): void
    {
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * route /manga/collection
     * @return void
     */
public function collection(?string $slug = null, ?string $numero = null): void
{
    $mangaModel = new MangaModel;

    if ($slug === null || $slug === '') {
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

    if ($numero !== null && $numero !== '') {
        $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

        if (!$manga) {
            http_response_code(404);
            exit('Manga introuvable');
        }

        $goodSlug = strtolower(str_replace(' ', '-', trim($manga->livre)));

        if ($goodSlug !== strtolower(trim($slug))) {
            header("Location: /lolissr/manga/collection/" . $goodSlug . "/" . $manga->numero);
            exit;
        }

        $this->render('manga/livre', [
            'manga' => $manga
        ]);
        return;
    }

    $mangas = $mangaModel->findBySlug($slug);

    $this->render('manga/collection', [
        'mangas' => $mangas,
        'titleFilter' => $slug
    ]);
}

    /**
     * route /manga/page/{id}
     * @param int $id
     * @return void
     */
    public function page(string $id): void
    {
        $id = (int) $id;

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
     * route /manga/lien
     * @return void
     */
    public function lien(): void
    {
        $this->title = 'Manga | Lien';
        $this->render('manga/lien');
    }


public function ajouter(): void
{
    $this->title = 'Manga | Ajouter';
    $this->render('manga/ajouter');
}

public function ajouterTraitement(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . $this->basePath . 'manga/ajouter');
        exit;
    }

    $livre = trim($_POST['livre'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $numero = (int) ($_POST['numero'] ?? 0);

    if ($livre === '' || $slug === '' || $numero <= 0 || empty($_FILES['image']['name'])) {
        exit('Formulaire incomplet');
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        exit('Erreur fichier');
    }

    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if ($extension === 'jpeg') {
        $extension = 'jpg';
    }

    $extensionsAutorisees = ['jpg', 'png', 'webp'];

    if (!in_array($extension, $extensionsAutorisees, true)) {
        exit('Format image non autorisé');
    }

    $thumbnail = preg_replace('/[^A-Za-z0-9\- ]/', '', strtoupper($livre));
    $thumbnail .= ' ' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
    $thumbnail = preg_replace('/\s+/', ' ', $thumbnail);
    $thumbnail = trim($thumbnail);

    $nomFichier = $thumbnail . '.' . $extension;

    $dossier = dirname(__DIR__) . '/public/images/mangas/thumbnail/';

    if (!is_dir($dossier)) {
        exit('Dossier image introuvable : ' . $dossier);
    }

    $destination = $dossier . $nomFichier;

    if (file_exists($destination)) {
        exit('Une image avec ce nom existe déjà');
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        exit('Erreur lors de l\'upload');
    }

    $mangaModel = new MangaModel();

    $mangaModel->insert([
        'thumbnail' => $thumbnail,
        'extension' => $extension,
        'slug' => strtolower($slug),
        'livre' => $livre,
        'numero' => $numero
    ]);

    $_SESSION['success'] = 'Manga ajouté avec succès';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /lolissr/manga/ajouter');
        exit;
}
}

public function edit(string $slug, string $numero): void
{
    $numero = (int) $numero;

    $mangaModel = new MangaModel;
    $manga = $mangaModel->findOneBySlugAndNumero($slug, $numero);

    if (!$manga) {
        http_response_code(404);
        exit('Manga introuvable');
    }

    $this->render('manga/edit', [
        'manga' => $manga
    ]);
}

public function update(string $slug, string $numero): void
{
    $numero = (int) $numero;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /lolissr/manga/collection/' . $slug . '/' . $numero);
        exit;
    }

    $note = $_POST['note'] ?? null;

    if ($note === '') {
        $note = null;
    } else {
        $note = (int) $note;

        if ($note < 1 || $note > 5) {
            exit('Note invalide');
        }
    }

    $mangaModel = new MangaModel;
    $mangaModel->updateNote($slug, $numero, $note);

    header('Location: /lolissr/manga/collection/' . $slug . '/' . $numero);
    exit;
}

}
