<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

final class JsonResponseTest extends TestCase
{
    public function testSuccess(): void
    {
        $response =
            JsonResponse::success(
                [
                    'title' => 'Rave',
                ],
            );

        $data =
            $response->data();

        $this->assertTrue(
            $data['success'],
        );

        $this->assertSame(
            'Rave',
            $data['title'],
        );
    }

    public function testError(): void
    {
        $response =
            JsonResponse::error(
                'Erreur AJAX',
                400,
            );

        $data =
            $response->data();

        $this->assertFalse(
            $data['success'],
        );

        $this->assertSame(
            'Erreur AJAX',
            $data['message'],
        );
    }

    public function testStatus(): void
    {
        $response =
            JsonResponse::success(
                [],
                201,
            );

        $this->assertSame(
            201,
            $response->status(),
        );

        $this->assertSame(
            201,
            $response->getStatusCode(),
        );
    }
}