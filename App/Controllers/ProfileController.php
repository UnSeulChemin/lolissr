<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Profil';

        $this->render(
            'pages/profile/index',
        );
    }
}