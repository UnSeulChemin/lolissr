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
    private ChinoisController $controller;

    protected function setUp(): void
    {
        $this->controller =
            new ChinoisController(
                new FakeChinoisReadService(),
                new FakeChinoisRepository(),
                new Request(),
            );
    }

    public function testMethodsExist(): void
    {
        $this->assertTrue(
            method_exists(
                $this->controller,
                'mandarin',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'jinyu',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'grammaire',
            ),
        );

        $this->assertTrue(
            method_exists(
                $this->controller,
                'flashcards',
            ),
        );
    }

    public function testInvalidHskLevel(): void
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
        $this->assertInstanceOf(
            ChinoisController::class,
            $this->controller,
        );
    }
}

final class FakeChinoisReadService extends ChinoisReadService
{
    public function __construct()
    {
    }

    public function mandarin(): array
    {
        return [];
    }

    public function jinyu(): array
    {
        return [];
    }
}

final class FakeChinoisRepository extends ChinoisGrammaireRepository
{
    public function __construct()
    {
    }

    public function findByLevel(
        string $level,
    ): array {
        return [];
    }
}