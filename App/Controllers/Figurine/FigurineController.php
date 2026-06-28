<?php

declare(strict_types=1);

namespace App\Controllers\Figurine;

use App\Controllers\Controller;
use Framework\Http\Request;

final class FigurineController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Figurines';

        $this->render('pages/figurine/index');
    }
}