<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Auth\LoginResult;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Services\Auth\LoginThrottleService;

use Framework\Http\Requests\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly LoginThrottleService $loginThrottleService,
        Request $request
    )
    {
        parent::__construct($request);
    }

    // =================================================
    // AUTHENTIFICATION
    // =================================================

    public function login(): never
    {
        $this->title = 'Connexion';

        $this->render('pages/auth/login', ['form' => $this->formViewData('connexion', '')]);
    }

    public function authenticate(): never
    {
        $username = $this->request->input('username');
        $password = $this->request->input('password');
        if (! is_string($username) || ! is_string($password))
        {
            $this->redirectWithError('connexion', 'Identifiants invalides.');
        }
        $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        $result = $this->authService->login($username, $password, $ipAddress);

        if ($result === LoginResult::LOCKED)
        {
            $remainingMinutes = $this->loginThrottleService->remainingLockMinutes($username, $ipAddress);

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

    // =================================================
    // INSCRIPTION
    // =================================================

    public function register(): never
    {
        $this->title = 'Inscription';

        $this->render('pages/auth/register', ['form' => $this->formViewData('inscription', '')]);
    }

    public function store(): never
    {
        $username = $this->request->input('username');
        $password = $this->request->input('password');
        if (! is_string($username) || ! is_string($password))
        {
            $this->redirectWithError('inscription', 'Identifiants invalides.');
        }

        $success = $this->authService->register($username, $password);

        if (! $success)
        {
            $this->redirectWithError('inscription', 'Impossible de créer le compte.');
        }

        $this->redirectWithSuccess('connexion', 'Compte créé avec succès.');
    }
}
