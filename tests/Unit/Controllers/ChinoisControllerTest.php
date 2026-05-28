<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\Chinois\ChinoisController;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class ChinoisControllerTest
{
    public static function run(): array
    {
        return [

            self::testMethodsExist(),

            self::testInvalidHskLevel(),

            self::testControllerInstantiation(),

        ];
    }

    private static function testMethodsExist(): array
    {
        $controller =
            self::controller();

        return [
            'name' =>
                'ChinoisController methods exist',

            'success' =>

                method_exists(
                    $controller,
                    'mandarin',
                )

                && method_exists(
                    $controller,
                    'jinyu',
                )

                && method_exists(
                    $controller,
                    'grammaire',
                )

                && method_exists(
                    $controller,
                    'flashcards',
                ),
        ];
    }

    private static function testInvalidHskLevel(): array
    {
        $success = false;

        try {

            self::controller()
                ->hsk(
                    99,
                );

        } catch (NotFoundException) {

            $success = true;
        }

        return [
            'name' =>
                'ChinoisController invalid HSK',

            'success' =>
                $success,
        ];
    }

    private static function testControllerInstantiation(): array
    {
        $controller =
            self::controller();

        return [
            'name' =>
                'ChinoisController instantiation',

            'success' =>
                $controller instanceof ChinoisController,
        ];
    }

    private static function controller(): ChinoisController
    {
        return new ChinoisController(
            new FakeChinoisReadService(),
            new FakeChinoisRepository(),
            new Request(),
        );
    }
}

final class FakeChinoisReadService
{
    public function mandarin(): array
    {
        return [];
    }

    public function jinyu(): array
    {
        return [];
    }
}

final class FakeChinoisRepository
{
    public function findByLevel(
        string $level,
    ): array {
        return [];
    }
}