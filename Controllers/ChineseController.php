<?php
namespace App\Controllers;

use App\Models\ChineseModel;

class ChineseController extends Controller
{
    /**
     * route /chinese
     * @return void
     */
    public function index(): void
    {
        // view
        $this->title = 'LoliSSR | Chinese';
        $this->render('chinese/index');
    }

    /**
     * route /chinese/vocabulary
     * @return void
     */
    public function vocabulary(): void
    {
        // class instance
        $chineseModel = new ChineseModel;
        $chineses = $chineseModel->findAll();

        // view
        $this->title = 'LoliSSR | Chinese | Vocabulary';
        $this->render('chinese/vocabulary', ['chineses' => $chineses]);
    }
}