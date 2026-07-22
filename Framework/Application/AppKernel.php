<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Http\Middleware\SecurityHeadersMiddleware;
use Framework\Http\Request;
use Framework\Routing\Router;
use Framework\Support\Session;

final readonly class AppKernel
{
    public function __construct(
        private Router $router,
        private Request $request,
        private SecurityHeadersMiddleware $securityHeaders
    ) {}

    // =========================================
    // APPLICATION
    // =========================================

    public function boot(): void
    {
        Session::start();

        date_default_timezone_set(App::timezone());

        $this->securityHeaders->handle($this->request);
    }

    public function handle(): void
    {
        $this->router->dispatch();
    }
}