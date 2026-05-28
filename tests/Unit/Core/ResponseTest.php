<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testJsonEncoding(): void
    {
        $json =
            json_encode(
                [
                    'success' => true,
                ],
            );

        $this->assertNotFalse(
            $json,
        );
    }

    public function testUtf8(): void
    {
        $json =
            json_encode(
                [
                    'title' => 'Élite',
                ],
                JSON_UNESCAPED_UNICODE,
            );

        $this->assertStringContainsString(
            'Élite',
            (string) $json,
        );
    }

    public function testRedirectUrl(): void
    {
        $url =
            '/manga/rave';

        $this->assertStringStartsWith(
            '/',
            $url,
        );
    }
}