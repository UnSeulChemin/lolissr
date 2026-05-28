<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\Manga\MangaController;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;

final class MangaControllerTest
{
    public static function run(): array
    {
        return [

            self::testSearchMethodExists(),

            self::testShowSeriesNotFound(),

            self::testShowNotFound(),

        ];
    }

    private static function testSearchMethodExists(): array
    {
        $controller =
            self::controller();

        return [
            'name' =>
                'MangaController search method exists',

            'success' =>
                method_exists(
                    $controller,
                    'search',
                ),
        ];
    }

    private static function testShowSeriesNotFound(): array
    {
        $success = false;

        try {

            self::controller()
                ->showSeries(
                    'unknown',
                );

        } catch (NotFoundException) {

            $success = true;
        }

        return [
            'name' =>
                'MangaController showSeries not found',

            'success' =>
                $success,
        ];
    }

    private static function testShowNotFound(): array
    {
        $success = false;

        try {

            self::controller()
                ->show(
                    'unknown',
                    1,
                );

        } catch (NotFoundException) {

            $success = true;
        }

        return [
            'name' =>
                'MangaController show not found',

            'success' =>
                $success,
        ];
    }

    private static function controller(): MangaController
    {
        return new MangaController(
            new FakeMangaReadService(),
            new FakeMangaWriteService(),
            new Request(),
        );
    }
}

final class FakeMangaReadService
{
    public function search(
        string $query,
    ): object {

        return (object) [
            'search' =>
                $query,

            'mangas' =>
                [],
        ];
    }

    public function showSeries(
        string $slug,
    ): ?object {
        return null;
    }

    public function one(
        string $slug,
        int $numero,
    ): ?object {
        return null;
    }

    public function series(
        int|string $page,
    ): ?object {
        return null;
    }
}

final class FakeMangaWriteService
{
}