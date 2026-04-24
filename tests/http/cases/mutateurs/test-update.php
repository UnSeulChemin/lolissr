<?php

declare(strict_types=1);

if ($testPostUpdate)
{
    addPostCheck($postChecks, [

        'category' => 'Update',
        'label' => 'Update bloqué en mode test',

        'url' => rtrim($base, '/') . '/manga/modifier/' . $realSlug . '/' . $realNumero,

        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'jacquette' => '5',
                    'livre_note' => '4',
                    'commentaire' => 'Test update automatique',
                ],
                [],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/modifier/' . $realSlug . '/' . $realNumero,
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                    'Accept: application/json',
                ],
                $body
            );

            $json = decodeJsonResponse($response['body']);

            $ok =
                $response['status'] === 403
                && is_array($json)
                && ($json['success'] ?? null) === false
                && isset($json['message'])
                && is_string($json['message']);

            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'],
            ];
        }

    ]);

    addPostCheck($postChecks, [

        'category' => 'Update',
        'label' => 'Update invalide (note > 5)',

        'url' => rtrim($base, '/') . '/manga/modifier/' . $realSlug . '/' . $realNumero,

        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'jacquette' => '9',
                    'livre_note' => '2',
                ],
                [],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/modifier/' . $realSlug . '/' . $realNumero,
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                    'Accept: application/json',
                ],
                $body
            );

            $json = decodeJsonResponse($response['body']);

            $ok =
                $response['status'] === 422
                && is_array($json)
                && ($json['success'] ?? null) === false
                && isset($json['errors'])
                && is_array($json['errors']);

            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'],
            ];
        }

    ]);

    addPostCheck($postChecks, [

        'category' => 'Update',
        'label' => 'Update slug non canonique',

        'url' => rtrim($base, '/') . '/manga/modifier/' . $nonCanonicalSlug . '/' . $realNumero,

        'callback' => static function () use ($base, $nonCanonicalSlug, $realNumero, $realSlug): array
        {
            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'jacquette' => '3',
                    'livre_note' => '3',
                ],
                [],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/modifier/' . $nonCanonicalSlug . '/' . $realNumero,
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                    'Accept: application/json',
                ],
                $body
            );

            $json = decodeJsonResponse($response['body']);

            $ok =
                $response['status'] === 409
                && is_array($json)
                && ($json['success'] ?? null) === false
                && isset($json['redirect'])
                && is_string($json['redirect'])
                && str_contains(
                    $json['redirect'],
                    '/manga/modifier/' . rawurlencode($realSlug) . '/' . $realNumero
                );

            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'],
            ];
        }

    ]);
}