<?php

declare(strict_types=1);

use App\Repositories\MangaRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class MangaRepositoryTest extends TestCase
{
    private MangaRepository $repository;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->repository = new MangaRepository();
        $this->reflection = new ReflectionClass($this->repository);
    }

    public function testNormalizeNoteValueReturnsNullWhenValueIsNull(): void
    {
        $this->assertNull($this->callPrivate('normalizeNoteValue', null));
    }

    public function testNormalizeNoteValueReturnsNullWhenValueIsEmpty(): void
    {
        $this->assertNull($this->callPrivate('normalizeNoteValue', ''));
    }

    public function testNormalizeNoteValueReturnsNullWhenValueIsOutOfRange(): void
    {
        $this->assertNull($this->callPrivate('normalizeNoteValue', 0));
        $this->assertNull($this->callPrivate('normalizeNoteValue', 6));
        $this->assertNull($this->callPrivate('normalizeNoteValue', -1));
    }

    public function testNormalizeNoteValueReturnsIntegerWhenValueIsValid(): void
    {
        $this->assertSame(1, $this->callPrivate('normalizeNoteValue', 1));
        $this->assertSame(3, $this->callPrivate('normalizeNoteValue', '3'));
        $this->assertSame(5, $this->callPrivate('normalizeNoteValue', 5));
    }

    public function testCalculateNoteReturnsNullWhenJacquetteIsNull(): void
    {
        $this->assertNull($this->callPrivate('calculateNote', null, 4));
    }

    public function testCalculateNoteReturnsNullWhenLivreNoteIsNull(): void
    {
        $this->assertNull($this->callPrivate('calculateNote', 4, null));
    }

    public function testCalculateNoteReturnsNullWhenBothAreNull(): void
    {
        $this->assertNull($this->callPrivate('calculateNote', null, null));
    }

    public function testCalculateNoteReturnsSumWhenBothNotesAreValid(): void
    {
        $this->assertSame(2, $this->callPrivate('calculateNote', 1, 1));
        $this->assertSame(7, $this->callPrivate('calculateNote', 3, 4));
        $this->assertSame(10, $this->callPrivate('calculateNote', 5, 5));
    }

    private function callPrivate(string $method, mixed ...$args): mixed
    {
        $reflectionMethod = $this->reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($this->repository, ...$args);
    }
}