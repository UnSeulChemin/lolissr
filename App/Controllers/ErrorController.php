<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;

final class ErrorController extends Controller
{
    /**
     * @var array<int, array{
     *     view: string,
     *     title: string
     * }>
     */
    private const ERRORS = [
        403 => [
            'view' => '403',
            'title' => '403 | Accès interdit'
        ],
        404 => [
            'view' => '404',
            'title' => '404 | Page introuvable'
        ],
        405 => [
            'view' => '405',
            'title' => '405 | Méthode non autorisée'
        ],
        419 => [
            'view' => '419',
            'title' => '419 | Session expirée'
        ],
        422 => [
            'view' => '422',
            'title' => '422 | Erreur de validation'
        ],
        500 => [
            'view' => '500',
            'title' => '500 | Erreur serveur'
        ]
    ];

    private function __construct(Request $request)
    {
        parent::__construct($request);
    }

    // =========================================
    // RENDU
    // =========================================

    public static function handle(int $status, string $message, Request $request): never
    {
        $controller = new self($request);

        $controller->renderErrorStatus($status, $message);
    }

    private function renderErrorStatus(int $status, string $message): never
    {
        if ($status === 401)
        {
            $this->redirect('connexion');
        }

        if ($status === 409)
        {
            $this->redirect('/');
        }

        $error = self::ERRORS[$status] ?? self::ERRORS[500];

        $this->title = $error['title'];

        $this->renderError(
            $error['view'],
            $status,
            [
                'message' => $message
            ]
        );
    }
}