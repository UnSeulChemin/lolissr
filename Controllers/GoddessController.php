<?php
namespace App\Controllers;

use App\Models\GoddessModel;
use App\Core\Functions;

class GoddessController extends Controller
{
    /**
     * route /goddess
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Goddess Story';
        $this->render('goddess/index');
    }

    /**
     * route /goddess/list
     * @return void
     */
    public function list(): void
    {
        // class instance
        $goddessModel = new GoddessModel;
        $goddesss = $goddessModel->findAllPaginate('id DESC', 20, 1);
        $count = $goddessModel->countPaginate(20);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Goddess Story List';
        $this->render('goddess/list', ['goddesss' => $goddesss, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /goddess/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // class instance
        $goddessModel = new GoddessModel;
        $goddesss = $goddessModel->findAllPaginate('id DESC', 20, $id);
        $count = $goddessModel->countPaginate(20);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Goddess Story List '.$id;
        $this->render('goddess/list', ['goddesss' => $goddesss, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /goddess/character/{id}
     * @param int $id
     * @return void
     */
    public function character(int $id): void
    {
        // class instance
        $goddessModel = new GoddessModel;
        $goddess = $goddessModel->find($id);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | '.$goddess->character;
        $this->render('goddess/character', ['goddess' => $goddess, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /goddess/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Goddess Link';
        $this->render('goddess/link');
    }
}