<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPDATE
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'Update',
    'label' => 'POST ajax update note',
    'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero, $testPostUpdate): array
    {
        if (!$testPostUpdate)
        {
            return [
                'ok' => true,
                'warn' => true,
                'message' => 'skippé (option désactivée)',
            ];
        }

        $payload = http_build_query([
            'jacquette' => '5',
            'livre_note' => '5',
            'commentaire' => 'Test update auto'
        ]);

        $response = requestUrl(
            $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);

        return [
            'ok' => $response['status'] === 200 && is_array($json) && !empty($json['success']),
            'message' => 'status ' . $response['status'],
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Update',
    'label' => 'Page modifier contient jacquette',
    'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/update/' . $realSlug . '/' . $realNumero);

        return [
            'ok' => $response['status'] === 200 && stripos($response['body'], 'name="jacquette"') !== false,
            'message' => $response['status'] === 200 ? 'champ trouvé' : 'page modifier inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Update',
    'label' => 'Page modifier contient livre_note',
    'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/update/' . $realSlug . '/' . $realNumero);

        return [
            'ok' => $response['status'] === 200 && stripos($response['body'], 'name="livre_note"') !== false,
            'message' => $response['status'] === 200 ? 'champ trouvé' : 'page modifier inaccessible',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'Update',
    'label' => 'Page modifier contient commentaire',
    'url' => $base . '/manga/update/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/update/' . $realSlug . '/' . $realNumero);

        return [
            'ok' => $response['status'] === 200 && stripos($response['body'], 'name="commentaire"') !== false,
            'message' => $response['status'] === 200 ? 'champ trouvé' : 'page modifier inaccessible',
        ];
    },
]);