<?php
namespace App\Controllers;

use App\Models\PlushModel;
use App\Core\Functions;

class PlushController extends Controller
{
    /**
     * route /plush
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Plush';
        $this->render('plush/index');
    }

    /**
     * route /plush/list
     * @return void
     */
    public function list(): void
    {
        // class instance
        $plushdModel = new PlushModel;
        $plushs = $plushdModel->findAllPaginate('id DESC', 8, 1);
        $count = $plushdModel->countPaginate(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Plush List';
        $this->render('plush/list', ['plushs' => $plushs, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /plush/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // class instance
        $plushdModel = new PlushModel;
        $plushs = $plushdModel->findAllPaginate('id DESC', 8, $id);
        $count = $plushdModel->countPaginate(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Plush List '.$id;
        $this->render('plush/list', ['plushs' => $plushs, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /plush/character/{id}
     * @param int $id
     * @return void
     */
    public function character(int $id): void
    {
        // class instance
        $plushdModel = new PlushModel;
        $plush = $plushdModel->find($id);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | '.$plush->character;
        $this->render('plush/character', ['plush' => $plush, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /plush/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Plush Link';
        $this->render('plush/link');
    }
}