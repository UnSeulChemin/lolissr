<?php

declare(strict_types=1);

namespace App\Controllers\Peluche;

use App\Controllers\Controller;
use Framework\Http\Request;

final class PelucheController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Peluches';

        $this->render('pages/peluche/index');
    }
}