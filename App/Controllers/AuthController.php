<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth\AuthService;
use Framework\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        Request $request,
    ) {
        parent::__construct($request);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(): never
    {
        $this->title = 'Connexion';

        $this->render(
            'pages/auth/connexion',
        );
    }

    public function authenticate(): never
    {
        $success =
            $this->authService->login(
                (string) $this->request->input('username'),
                (string) $this->request->input('password'),
            );

        if (! $success)
        {
            $this->redirectWithError(
                'connexion',
                'Identifiants invalides.',
            );
        }

        $this->redirect('/');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(): never
    {
        if (
            env('APP_ENV') === 'production'
        ) {
            $this->notFound();
        }

        $this->title = 'Inscription';

        $this->render(
            'pages/auth/inscription',
        );
    }

    public function store(): never
    {
        if (
            env('APP_ENV') === 'production'
        ) {
            $this->notFound();
        }

        $success =
            $this->authService->register(
                (string) $this->request->input('username'),
                (string) $this->request->input('password'),
            );

        if (! $success)
        {
            $this->redirectWithError(
                'inscription',
                'Impossible de créer le compte.',
            );
        }

        $this->redirectWithSuccess(
            'connexion',
            'Compte créé avec succès.',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(): never
    {
        $this->authService->logout();

        $this->redirect(
            'connexion',
        );
    }
}