<?php

declare(strict_types=1);

namespace App\Controllers;

class ErrorController extends Controller
{
    public function renderCsrfExpiredPage(): void
{
    $this->renderError('errors/419', 419, [
        'title' => '419 | Session expirée',
        'message' => 'Session expirée ou requête invalide.',
    ]);
}
}