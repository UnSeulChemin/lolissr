<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;

use Framework\Http\Request;

final class ChinoisController extends Controller
{
    public function __construct(
        Request $request
    ) {
        parent::__construct($request);
    }

    // =========================================
    // PAGES
    // =========================================

    public function index(): never
    {
        $this->title = 'Chinois';

        $this->render(
            'pages/chinois/index'
        );
    }

    // =========================================
    // AJOUT
    // =========================================

    public function ajouter(): never
    {
        $this->title = 'Chinois | Ajouter';

        $this->render(
            'pages/chinois/ajouter/index'
        );
    }
}