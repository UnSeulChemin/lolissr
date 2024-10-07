<?php
namespace App\Controllers;

use App\Models\NendoroidModel;
use App\Core\Functions;

class NendoroidController extends Controller
{
    /**
     * route /nendoroid
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Nendoroid';
        $this->render('nendoroid/index');
    }

    /**
     * route /nendoroid/list
     * @return void
     */
    public function list(): void
    {
        // class instance
        $nendoroidModel = new NendoroidModel;
        $nendoroids = $nendoroidModel->findAllPaginateIsObtained('id DESC', 8, 1);
        $count = $nendoroidModel->countPaginateIsObtained(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Nendoroid List';
        $this->render('nendoroid/list', ['nendoroids' => $nendoroids, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /nendoroid/page/{id}
     * @param int $id
     * @return void
     */
    public function page(int $id): void
    {
        // class instance
        $nendoroidModel = new NendoroidModel;
        $nendoroids = $nendoroidModel->findAllPaginateIsObtained('id DESC', 8, $id);
        $count = $nendoroidModel->countPaginateIsObtained(8);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Nendoroid List '.$id;
        $this->render('nendoroid/list', ['nendoroids' => $nendoroids, 'count' => $count, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /nendoroid/wish
     * @return void
     */
    public function wish(): void
    {
        // class instance
        $nendoroidModel = new NendoroidModel;
        $nendoroids = $nendoroidModel->findBy(['obtained' => 'N']);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | Nendoroid Wish';
        $this->render('nendoroid/wish', ['nendoroids' => $nendoroids, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /nendoroid/character/{id}
     * @param int $id
     * @return void
     */
    public function character(int $id): void
    {
        // class instance
        $nendoroidModel = new NendoroidModel;
        $nendoroid = $nendoroidModel->find($id);

        // functions static
        $pathRedirect = Functions::getPathRedirect();

        // view
        $this->title = 'LoliSSR | '.$nendoroid->serie;
        $this->render('nendoroid/character', ['nendoroid' => $nendoroid, 'pathRedirect' => $pathRedirect]);
    }

    /**
     * route /nendoroid/link
     * @return void
     */
    public function link(): void
    {
        // view
        $this->title = 'LoliSSR | Nendoroid Link';
        $this->render('nendoroid/link');
    }
}