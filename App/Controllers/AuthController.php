<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth\AuthService;

use Framework\Http\Request;

final class AuthController extends Controller
{
    private const PRODUCTION_ENV = 'production';

    public function __construct(
        private readonly AuthService $authService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    public function login(): never
    {
        $this->title = 'Connexion';
        $this->render('pages/auth/connexion');
    }

    public function authenticate(): never
    {
        $success = $this->authService->login(
            (string) $this->request->input('username'),
            (string) $this->request->input('password'),
        );

        if (! $success)
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
    | REGISTRATION
    |--------------------------------------------------------------------------
    */

    public function register(): never
    {
        $this->guardRegistration();

        $this->title = 'Inscription';
        $this->render('pages/auth/inscription');
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
        if (env('APP_ENV') === self::PRODUCTION_ENV)
        {
            $this->notFound();
        }
    }
}
