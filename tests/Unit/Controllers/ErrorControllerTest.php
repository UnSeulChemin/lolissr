<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\ErrorController;

final class ErrorControllerTest extends TestCase
{
    public function testNotFoundSetsCorrectValues(): void
    {
        $controller = new TestableErrorController();

        $controller->notFound('Test 404');

        $this->assertSame('404', $controller->view);
        $this->assertSame(404, $controller->code);
        $this->assertSame(
            ['message' => 'Test 404'],
            $controller->data
        );

        $this->assertSame(
            '404 | Page introuvable',
            $controller->getTitleValue()
        );
    }

    public function testMethodNotAllowedSetsCorrectValues(): void
    {
        $controller = new TestableErrorController();

        $controller->methodNotAllowed('Test 405');

        $this->assertSame('405', $controller->view);
        $this->assertSame(405, $controller->code);
        $this->assertSame(
            ['message' => 'Test 405'],
            $controller->data
        );

        $this->assertSame(
            '405 | Méthode non autorisée',
            $controller->getTitleValue()
        );
    }

    public function testServerErrorSetsCorrectValues(): void
    {
        $controller = new TestableErrorController();

        $controller->serverError('Test 500');

        $this->assertSame('500', $controller->view);
        $this->assertSame(500, $controller->code);
        $this->assertSame(
            ['message' => 'Test 500'],
            $controller->data
        );

        $this->assertSame(
            '500 | Erreur serveur',
            $controller->getTitleValue()
        );
    }
}

final class TestableErrorController extends ErrorController
{
    public string $view = '';
    public int $code = 0;
    public array $data = [];

    protected function renderError(
        string $view,
        int $code,
        array $data = []
    ): void
    {
        $this->view = $view;
        $this->code = $code;
        $this->data = $data;
    }

    public function getTitleValue(): string
    {
        return $this->title;
    }
}