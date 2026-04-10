<?php
namespace App\Controllers;

use App\Models\MangaModel;
use App\Core\Functions;

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
    public function collection(): void
    {
        // class instance
        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllPaginate('id DESC', 8, 1);
        $compteur = $mangaModel->countPaginate(8);

        // functions static
        $routeRedirection = Functions::getPathRedirect();

        // view
        $this->title = 'Manga | Collection';
        $this->render('manga/collection', ['mangas' => $mangas, 'compteur' => $compteur, 'routeRedirection' => $routeRedirection]);
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
        $mangas = $mangaModel->findAllPaginate('id DESC', 8, $id);
        $compteur = $mangaModel->countPaginate(8);

        // functions static
        $routeRedirection = Functions::getPathRedirect();

        // view
        $this->title = 'Manga | Collection Page '.$id;
        $this->render('plush/list', ['mangas' => $mangas, 'compteur' => $compteur, 'routeRedirection' => $routeRedirection]);
    }

    /**
     * route /manga/livre/{id}
     * @param int $id
     * @return void
     */
    public function livre(int $id): void
    {
        // class instance
        $mangaModel = new MangaModel;
        $manga = $mangaModel->find($id);

        // functions static
        $routeRedirection = Functions::getPathRedirect();

        // view
        $this->title = 'Manga | '.$manga->livre;
        $this->render('manga/livre', ['manga' => $manga, 'routeRedirection' => $routeRedirection]);
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