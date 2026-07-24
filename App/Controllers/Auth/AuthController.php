<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Enums\Auth\LoginResult;
use App\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Services\Auth\LoginThrottleService;

use Framework\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoginThrottleService $loginThrottleService,
        Request $request
    ) {
        parent::__construct($request);
    }

    /*
    |-------------------------------------------------------------------------- 
    | AUTHENTIFICATION
    |-------------------------------------------------------------------------- 
    */

    public function login(): never
    {
        $this->title = 'Connexion';

        $this->render('pages/auth/connexion', [
            'form' => $this->formViewData(
                'connexion',
                '',
            ),
        ]);
    }

    public function authenticate(): never
    {
        $username = (string) $this->request->input('username');
        $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $result = $this->authService->login(
            $username,
            (string) $this->request->input('password'),
            $ipAddress
        );

        if ($result === LoginResult::LOCKED)
        {
            $remainingMinutes = $this->loginThrottleService->remainingLockMinutes(
                $username,
                $ipAddress
            );

            $this->redirectWithError(
                'connexion',
                "Trop de tentatives. Réessaie dans {$remainingMinutes} minute(s)."
            );
        }

        if ($result === LoginResult::INVALID_CREDENTIALS)
        {
            $this->redirectWithError('connexion', 'Identifiants invalides.');
        }

        $this->redirect('/');
    }

    public function logout(): never
    {
        $this->authService->logout();

        $this->redirect('connexion');
    }

    /*
    |-------------------------------------------------------------------------- 
    | INSCRIPTION
    |-------------------------------------------------------------------------- 
    */

    public function register(): never
    {
        $this->guardRegistration();

        $this->title = 'Inscription';

        $this->render('pages/auth/inscription', [
            'form' => $this->formViewData(
                'inscription',
                '',
            ),
        ]);
    }

    public function store(): never
    {
        $this->guardRegistration();

        $success = $this->authService->register(
            (string) $this->request->input('username'),
            (string) $this->request->input('password'),
        );

        if (! $success)
        {
            $this->redirectWithError('inscription', 'Impossible de créer le compte.');
        }

        $this->redirectWithSuccess('connexion', 'Compte créé avec succès.');
    }

    /*
    |-------------------------------------------------------------------------- 
    | HELPERS
    |-------------------------------------------------------------------------- 
    */

    private function guardRegistration(): void
    {
        if (env('APP_ENV') === 'production')
        {
            $this->notFound();
        }
    }
}