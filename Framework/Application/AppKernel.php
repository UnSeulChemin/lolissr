<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Debug\Profiler;
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
    ) {
    }

    // =========================================
    // APPLICATION
    // =========================================

    public function boot(): void
    {
        Profiler::start('kernel.boot');

        try
        {
            Session::start();

            $this->securityHeaders->handle($this->request);
        }
        finally
        {
            Profiler::end('kernel.boot');
        }
    }

    public function handle(): void
    {
        Profiler::start('router.dispatch');

        try
        {
            $this->router->dispatch();
        }
        finally
        {
            Profiler::end('router.dispatch');
        }
    }
}