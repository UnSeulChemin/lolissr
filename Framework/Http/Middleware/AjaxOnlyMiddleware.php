<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Framework\Application\App;
use Framework\Http\Request;

final class AjaxOnlyMiddleware implements MiddlewareInterface
{
    private const TEST_USER_AGENT =
        'LoliSSR-TestRunner';

    public function handle(
        Request $request,
    ): void {
        if (
            $request->isAjax()
            || $this->isTestRunner($request)
        ) {
            return;
        }

        json([
            'success' => false,
            'message' => 'Requête AJAX requise',
        ], 400);
    }

    private function isTestRunner(
        Request $request,
    ): bool {
        return App::isTesting()
            && str_contains(
                $request->userAgent(),
                self::TEST_USER_AGENT,
            );
    }
}