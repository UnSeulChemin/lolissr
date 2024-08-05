<?php
namespace App\Controllers;

use App\Models\FrenchModel;

class FrenchController extends Controller
{
    /**
     * route /french
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | French';
        $this->render('french/index');
    }

    /**
     * route /french/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // class instance
        $frenchModel = new FrenchModel;
        $frenchs = $frenchModel->findAll();

        // view
        $this->title = 'LoliSSR | French | Vocabulary';
        $this->render('french/vocabulary', ['frenchs' => $frenchs]);
    }
}