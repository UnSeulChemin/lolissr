<?php

declare(strict_types=1);

use App\Models\MangaModel;
use PHPUnit\Framework\TestCase;

final class MangaControllerTest extends TestCase
{
    public function testDebug(): void
    {
        $model = $this->createMock(MangaModel::class);

        $this->assertInstanceOf(MangaModel::class, $model);
    }
}