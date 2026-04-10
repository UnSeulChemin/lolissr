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
        // view
        $this->title = 'Manga';
        $this->render('manga/index');
    }

    /**
     * route /manga/collection
     * @return void
     */
public function collection(?string $slug = null, ?int $id = null): void
{
    $mangaModel = new MangaModel;

    // CAS 1 : tout
    if (!$slug) {
        $mangas = $mangaModel->findAllPaginateSearch('id DESC', 8, 1, '01');

        $this->render('manga/collection', [
            'mangas' => $mangas,
            'titleFilter' => null
        ]);
        return;
    }

    $slug = str_replace('-', ' ', $slug);

    // CAS 2 : ID + SLUG (VALIDATION OBLIGATOIRE)
    if ($id) {
        $manga = $mangaModel->find($id);

        // 🔥 IMPORTANT : vérifie que le manga correspond au slug
        if (strtolower($manga->livre) !== strtolower($slug)) {
            header("Location: /manga/collection/" . strtolower(str_replace(' ', '-', $manga->livre)) . "/$id");
            exit;
        }

        $this->render('manga/livre', [
            'manga' => $manga
        ]);
        return;
    }

    // CAS 3 : collection manga
    $mangas = $mangaModel->findByLivre($slug);

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
    public function page(int $id): void
    {
        // class instance
        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllPaginateSearch('id DESC', 8, $id, '01');
        $compteur = $mangaModel->countPaginate(8);

        // view
        $this->title = 'Manga | Collection Page '.$id;
        $this->render('plush/list', ['mangas' => $mangas, 'compteur' => $compteur]);
    }

    /**
     * route /manga/lien
     * @return void
     */
    public function lien(): void
    {
        // view
        $this->title = 'Manga | Lien';
        $this->render('manga/lien');
    }
}