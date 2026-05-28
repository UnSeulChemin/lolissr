<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Controllers\Chinois\ChinoisController;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Services\Chinois\ChinoisReadService;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class ChinoisControllerTest extends TestCase
{
    private ChinoisReadService $readService;

    private ChinoisGrammaireRepository $repository;

    private ChinoisController $controller;

    protected function setUp(): void
    {
        $this->readService =
            $this->createMock(
                ChinoisReadService::class,
            );

        $this->repository =
            $this->createMock(
                ChinoisGrammaireRepository::class,
            );

        $this->controller =
            new ChinoisController(
                $this->readService,
                $this->repository,
                new Request(),
            );
    }

    public function testMethodsExist(): void
    {
        self::assertTrue(
            method_exists(
                $this->controller,
                'mandarin',
            ),
        );

        self::assertTrue(
            method_exists(
                $this->controller,
                'jinyu',
            ),
        );

        self::assertTrue(
            method_exists(
                $this->controller,
                'grammaire',
            ),
        );

        self::assertTrue(
            method_exists(
                $this->controller,
                'flashcards',
            ),
        );
    }

    public function testInvalidHskLevelThrowsNotFound(): void
    {
        $this->expectException(
            NotFoundException::class,
        );

        $this->controller->hsk(
            99,
        );
    }

    public function testControllerInstantiation(): void
    {
        self::assertInstanceOf(
            ChinoisController::class,
            $this->controller,
        );
    }
}