<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\User\UserLevelService;
use Framework\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserLevelService $userLevelService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    public function index(): never
    {
        $this->title = 'Profil';

        $user = user();

        if ($user === null)
        {
            redirect('connexion');
        }

        $this->render(
            'pages/profile/index',
            [
                'user' => $user,
                'progress' => $this->userLevelService->progress(
                    $user,
                ),
                'xpRequired' => $this->userLevelService->xpRequiredForLevel(
                    $user->level,
                ),
            ],
        );
    }
}