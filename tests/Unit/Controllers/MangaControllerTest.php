<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\Manga\MangaController;
use App\Services\Manga\MangaReadService;
use App\Services\Manga\MangaWriteService;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class MangaControllerTest extends TestCase
{
    private MangaController $controller;

    protected function setUp(): void
    {
        $this->controller =
            new MangaController(
                new FakeMangaReadService(),
                new FakeMangaWriteService(),
                new Request(),
            );
    }

    public function testSearchMethodExists(): void
    {
        $this->assertTrue(
            method_exists(
                $this->controller,
                'search',
            ),
        );
    }

    public function testShowSeriesNotFound(): void
    {
        $this->expectException(
            NotFoundException::class,
        );

        $this->controller->showSeries(
            'unknown',
        );
    }

    public function testShowNotFound(): void
    {
        $this->expectException(
            NotFoundException::class,
        );

        $this->controller->show(
            'unknown',
            1,
        );
    }
}

final class FakeMangaReadService extends MangaReadService
{
    public function __construct()
    {
    }

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

final class FakeMangaWriteService extends MangaWriteService
{
    public function __construct()
    {
    }
}