<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TEST UPDATE
|--------------------------------------------------------------------------
|
| Test POST /manga/update/{slug}/{numero}
|
| Vérifie :
| - update valide
| - validation erreur
| - réponse AJAX correcte
|
*/

/*
|--------------------------------------------------------------------------
| Update valide
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [

    'category' => 'Update',
    'label' => 'Update notes valide',

    'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,

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
            $base . '/manga/update/' . $realSlug . '/' . $realNumero,
            'POST',
            [
                "Content-Type: multipart/form-data; boundary=$boundary",
                'X-Requested-With: XMLHttpRequest',
            ],
            $body
        );

        $json = json_decode($response['body'], true);

        $ok =
            $response['status'] === 200
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === true;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'],
        ];
    }

]);

/*
|--------------------------------------------------------------------------
| Update invalide (note hors limite)
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [

    'category' => 'Update',
    'label' => 'Update invalide (note > 5)',

    'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,

    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $boundary = uniqid('boundary_', true);

        $body = buildMultipartBody(
            [
                'jacquette' => '9', // invalide
                'livre_note' => '2',
            ],
            [],
            $boundary
        );

        $response = requestUrl(
            $base . '/manga/update/' . $realSlug . '/' . $realNumero,
            'POST',
            [
                "Content-Type: multipart/form-data; boundary=$boundary",
                'X-Requested-With: XMLHttpRequest',
            ],
            $body
        );

        $json = json_decode($response['body'], true);

        $ok =
            $response['status'] === 422
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'],
        ];
    }

]);

/*
|--------------------------------------------------------------------------
| Update slug non canonique
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [

    'category' => 'Update',
    'label' => 'Update slug non canonique',

    'url' => $base . '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,

    'callback' => static function () use ($base, $nonCanonicalSlug, $realNumero): array
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
            $base . '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,
            'POST',
            [
                "Content-Type: multipart/form-data; boundary=$boundary",
                'X-Requested-With: XMLHttpRequest',
            ],
            $body
        );

        $json = json_decode($response['body'], true);

        $ok =
            $response['status'] === 409
            && is_array($json)
            && isset($json['redirect']);

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'],
        ];
    }

]);