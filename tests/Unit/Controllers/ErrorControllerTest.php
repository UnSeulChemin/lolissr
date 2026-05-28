<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\ErrorController;
use Framework\Http\Request;

final class ErrorControllerTest
{
    public static function run(): array
    {
        return [

            self::testMethodsExist(),

            self::testControllerInstantiation(),

        ];
    }

    private static function testMethodsExist(): array
    {
        $controller =
            self::controller();

        return [
            'name' =>
                'ErrorController methods exist',

            'success' =>

                method_exists(
                    $controller,
                    'notFound',
                )

                && method_exists(
                    $controller,
                    'forbidden',
                )

                && method_exists(
                    $controller,
                    'unauthorized',
                )

                && method_exists(
                    $controller,
                    'serverError',
                ),
        ];
    }

    private static function testControllerInstantiation(): array
    {
        $controller =
            self::controller();

        return [
            'name' =>
                'ErrorController instantiation',

            'success' =>
                $controller instanceof ErrorController,
        ];
    }

    private static function controller(): ErrorController
    {
        return new ErrorController(
            new Request(),
        );
    }
}