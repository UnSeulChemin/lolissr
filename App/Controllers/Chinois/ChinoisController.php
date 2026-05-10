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
}