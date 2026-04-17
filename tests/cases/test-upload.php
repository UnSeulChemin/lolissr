<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPLOAD SAFE
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'Upload',
    'label' => 'POST ajouter sans fichier',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'livre' => 'Test Upload Safe',
            'slug' => 'test-upload-safe',
            'numero' => '321',
            'commentaire' => 'sans image',
        ]);

        $response = requestUrl(
            $base . '/manga/ajouter',
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);
        $statusOk = in_array($response['status'], [200, 400, 422], true);

        $hasErrorSignal = false;

        if (is_array($json))
        {
            $hasErrorSignal =
                !empty($json['errors'])
                || !empty($json['error'])
                || (isset($json['success']) && $json['success'] === false);
        }

        return [
            'ok' => $statusOk && (is_array($json) ? $hasErrorSignal : true),
            'message' => 'status ' . $response['status'] . ' | absence fichier refusée',
        ];
    },
]);