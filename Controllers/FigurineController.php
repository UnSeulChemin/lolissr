<?php
namespace App\Controllers;

use App\Models\FigurineModel;
use App\Core\Functions;

class FigurineController extends Controller
{
    /**
     * route /figurine
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Figurine';
        $this->render('figurine/index');
    }

    /**
     * route /figurine/list
     * @return void
     */
    public function list(): void
    {
        // class instance
        $figurineModel = new FigurineModel;
        $figurines = $figurineModel->findAllPaginate('id DESC', 8, 1);
        $count = $figurineModel->countPaginate(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Figurine List';
        $this->render('figurine/list', ['figurines' => $figurines, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /figurine/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // class instance
        $figurineModel = new FigurineModel;
        $figurines = $figurineModel->findAllPaginate('id DESC', 8, $id);
        $count = $figurineModel->countPaginate(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Figurine List '.$id;
        $this->render('figurine/list', ['figurines' => $figurines, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /figurine/character/{id}
     * @param int $id
     * @return void
     */
    public function character(int $id): void
    {
        // class instance
        $figurineModel = new FigurineModel;
        $figurine = $figurineModel->find($id);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | '.$figurine->character;
        $this->render('figurine/character', ['figurine' => $figurine, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /figurine/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Figurine Link';
        $this->render('figurine/link');
    }
}