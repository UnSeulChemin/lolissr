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
        $compteur = $mangaModel->countPaginate(8);

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
    public function page(int $id): void
    {
        $mangaModel = new MangaModel;
        $mangas = $mangaModel->findAllFirstTomes('id DESC', 8, $id);
        $compteur = $mangaModel->countPaginate(8);

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
}