<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Http\Request;
use Framework\Routing\Router;
use Framework\Support\Session;

final readonly class AppKernel
{
    public function __construct(
        private Router $router,
    ) {
    }

    public function boot(): void
    {
        Session::get('__boot__');

        date_default_timezone_set(
            App::timezone(),
        );
    }

    public function handle(
        Request $request,
    ): void {
        $this->router->dispatch();
    }

    public function terminate(): void
    {
        //
        // futur:
        // logs
        // events
        // queues
        // metrics
        //
    }
}