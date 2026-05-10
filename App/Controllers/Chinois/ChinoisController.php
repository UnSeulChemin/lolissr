<?php

declare(strict_types=1);

namespace App\Controllers\Chinois;

use App\Controllers\Controller;

final class ChinoisController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->title = 'Chinois';

        $this->render('chinois/index');
    }

    public function mandarin(): void
    {
        $this->title = 'Chinois | Mandarin';

        $this->render('chinois/mandarin');
    }

    public function jin(): void
    {
        $this->title = 'Chinois | 晋语';

        $this->render('chinois/jin');
    }

    public function flashcards(): void
    {
        $this->title = 'Chinois | Flashcards';

        $this->render('chinois/flashcards');
    }

    public function ajouter(): void
    {
        $this->title = 'Chinois | Ajouter';

        $this->render('chinois/ajouter');
    }
}