<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Trait\CreatedAtTrait;

final class CreatedAtTraitTest extends TestCase
{
    public function testGetCreatedAtReturnsNullByDefault(): void
    {
        $model = new TestableCreatedAt();

        $this->assertNull($model->getCreatedAt());
    }

    public function testSetCreatedAtWithValidDate(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt('2024-01-01 10:30:00');

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $model->getCreatedAt()
        );
    }

    public function testSetCreatedAtWithNull(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt(null);

        $this->assertNull($model->getCreatedAt());
    }

    public function testSetCreatedAtWithEmptyString(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt('');

        $this->assertNull($model->getCreatedAt());
    }

    public function testSetCreatedAtWithInvalidDate(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt('invalid-date');

        $this->assertNull($model->getCreatedAt());
    }

    public function testGetCreatedAtFormattedReturnsFormattedDate(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt('2024-01-01 10:30:00');

        $this->assertSame(
            '01/01/2024 10:30',
            $model->getCreatedAtFormatted()
        );
    }

    public function testGetCreatedAtFormattedWithCustomFormat(): void
    {
        $model = new TestableCreatedAt();

        $model->setCreatedAt('2024-01-01 10:30:00');

        $this->assertSame(
            '2024-01-01',
            $model->getCreatedAtFormatted('Y-m-d')
        );
    }

    public function testGetCreatedAtFormattedReturnsNullWhenDateIsNull(): void
    {
        $model = new TestableCreatedAt();

        $this->assertNull(
            $model->getCreatedAtFormatted()
        );
    }
}

/**
 * Classe fake pour tester le trait.
 */
final class TestableCreatedAt
{
    use CreatedAtTrait;
}