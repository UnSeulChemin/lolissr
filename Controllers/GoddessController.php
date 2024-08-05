<?php
namespace App\Controllers;

use App\Models\GoddessModel;

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
     * route /goddess/cards
     * @return void
     */
    public function cards(): void
    {
        // class instance
        $goddessModel = new GoddessModel;
        $goddesss = $goddessModel->findAll();

        // view
        $this->title = 'LoliSSR | Goddess Story | Cards';
        $this->render('goddess/cards', ['goddesss' => $goddesss]);
    }
}