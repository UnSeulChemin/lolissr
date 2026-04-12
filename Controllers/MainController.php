<?php
namespace App\Controllers;

class MainController extends Controller
{
    /**
     * page d'accueil
     * route : /
     */
    public function index(): void
    {
        $this->render('layouts/index');
    }
}