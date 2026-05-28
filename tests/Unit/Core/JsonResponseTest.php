<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\JsonResponse;

final class JsonResponseTest
{
    public static function run(): array
    {
        return [

            self::testSuccess(),

            self::testError(),

            self::testStatus(),

            self::testGetStatusCode(),

        ];
    }

    private static function testSuccess(): array
    {
        $response = JsonResponse::success(
            [
                'title' => 'Rave',
            ],
        );

        $data =
            $response->data();

        return [
            'name' =>
                'JsonResponse success',

            'success' =>
                $data['success'] === true
                && $data['title'] === 'Rave',
        ];
    }

    private static function testError(): array
    {
        $response = JsonResponse::error(
            'Erreur AJAX',
            400,
        );

        $data =
            $response->data();

        return [
            'name' =>
                'JsonResponse error',

            'success' =>
                $data['success'] === false
                && $data['message']
                    === 'Erreur AJAX',
        ];
    }

    private static function testStatus(): array
    {
        $response = JsonResponse::success(
            [],
            201,
        );

        return [
            'name' =>
                'JsonResponse status',

            'success' =>
                $response->status()
                    === 201,
        ];
    }

    private static function testGetStatusCode(): array
    {
        $response = JsonResponse::success(
            [],
            204,
        );

        return [
            'name' =>
                'JsonResponse get status code',

            'success' =>
                $response->getStatusCode()
                    === 204,
        ];
    }
}