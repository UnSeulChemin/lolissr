<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\Manga\MangaSearchRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class MangaSearchRepositoryTest extends TestCase
{
    private MangaSearchRepository $repository;

    protected function setUp(): void
    {
        $this->repository =
            new MangaSearchRepository();
    }

    public function testNormalizeSearch(): void
    {
        $method =
            $this->privateMethod(
                'normalizeSearch',
            );

        $result =
            $method->invoke(
                $this->repository,
                '   one    piece   ',
            );

        $this->assertSame(
            'one piece',
            $result,
        );
    }

    public function testExtractNumero(): void
    {
        $method =
            $this->privateMethod(
                'extractSearchNumero',
            );

        $result =
            $method->invoke(
                $this->repository,
                'one piece 12',
            );

        $this->assertIsArray(
            $result,
        );

        $this->assertSame(
            'one piece',
            $result['title'],
        );

        $this->assertSame(
            12,
            $result['numero'],
        );
    }

    public function testExtractNumeroWithTome(): void
    {
        $method =
            $this->privateMethod(
                'extractSearchNumero',
            );

        $result =
            $method->invoke(
                $this->repository,
                'one piece tome 12',
            );

        $this->assertIsArray(
            $result,
        );

        $this->assertSame(
            12,
            $result['numero'],
        );
    }

    public function testInvalidNumero(): void
    {
        $method =
            $this->privateMethod(
                'extractSearchNumero',
            );

        $result =
            $method->invoke(
                $this->repository,
                'one piece',
            );

        $this->assertNull(
            $result,
        );
    }

    private function privateMethod(
        string $method,
    ): ReflectionMethod {

        $reflection =
            new ReflectionClass(
                $this->repository,
            );

        $method =
            $reflection->getMethod(
                $method,
            );

        $method->setAccessible(
            true,
        );

        return $method;
    }
}