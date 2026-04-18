<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Router;

final class RouterTest extends TestCase
{
    public function testDispatchThrowsExceptionWhenActionFormatIsInvalid(): void
    {
        $router = new Router();

        $router->get('/manga', 'ActionInvalide');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Action invalide');

        $router->dispatch('/manga', 'GET');
    }

    public function testDispatchThrowsExceptionWhenControllerDoesNotExist(): void
    {
        $router = new Router();

        $router->get('/manga', 'FakeController@index');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Controller introuvable');

        $router->dispatch('/manga', 'GET');
    }

    public function testDispatchThrowsExceptionWhenMethodDoesNotExist(): void
    {
        $router = new Router();

        $router->get('/manga', 'MangaController@fakeMethod');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Méthode introuvable');

        $router->dispatch('/manga', 'GET');
    }

    public function testDispatchMatchesDynamicRoutePattern(): void
    {
        $router = new Router();

        $router->get('/manga/{slug}/{numero}', 'MangaController@show');

        ob_start();

        try
        {
            $router->dispatch('/manga/one-piece/1', 'GET');
            ob_end_clean();
            $this->assertTrue(true);
        }
        catch (\RuntimeException $exception)
        {
            ob_end_clean();
            $this->fail(
                'La route dynamique aurait dû matcher. Exception RuntimeException : '
                . $exception->getMessage()
            );
        }
    }

    public function testDispatchAcceptsTrailingSlash(): void
    {
        $router = new Router();

        $router->get('/manga/{slug}/{numero}', 'MangaController@show');

        ob_start();

        try
        {
            $router->dispatch('/manga/one-piece/1/', 'GET');
            ob_end_clean();
            $this->assertTrue(true);
        }
        catch (\RuntimeException $exception)
        {
            ob_end_clean();
            $this->fail(
                'La route avec slash final aurait dû matcher. Exception RuntimeException : '
                . $exception->getMessage()
            );
        }
    }
}