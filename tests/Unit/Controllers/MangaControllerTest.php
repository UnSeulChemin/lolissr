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
    private MangaReadService $readService;

    private MangaWriteService $writeService;

    private MangaController $controller;

    protected function setUp(): void
    {
        $this->readService =
            $this->createMock(
                MangaReadService::class,
            );

        $this->writeService =
            $this->createMock(
                MangaWriteService::class,
            );

        $this->controller =
            new MangaController(
                $this->readService,
                $this->writeService,
                new Request(),
            );
    }

    public function testSearchMethodExists(): void
    {
        self::assertTrue(
            method_exists(
                $this->controller,
                'search',
            ),
        );
    }

    public function testShowSeriesThrowsNotFound(): void
    {
        $this->readService
            ->expects(self::once())
            ->method('showSeries')
            ->with('unknown')
            ->willReturn(null);

        $this->expectException(
            NotFoundException::class,
        );

        $this->controller->showSeries(
            'unknown',
        );
    }

    public function testShowThrowsNotFound(): void
    {
        $this->readService
            ->expects(self::once())
            ->method('one')
            ->with(
                'unknown',
                1,
            )
            ->willReturn(null);

        $this->expectException(
            NotFoundException::class,
        );

        $this->controller->show(
            'unknown',
            1,
        );
    }
}