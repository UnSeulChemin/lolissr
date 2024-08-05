<?php
namespace App\Controllers;

use App\Models\EnglishModel;

class EnglishController extends Controller
{
    /**
     * route /english
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | English';
        $this->render('english/index');
    }

    /**
     * route /english/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // class instance
        $englishModel = new EnglishModel;
        $englishs = $englishModel->findAll();

        // view
        $this->title = 'LoliSSR | English | Vocabulary';
        $this->render('english/vocabulary', ['englishs' => $englishs]);
    }
}