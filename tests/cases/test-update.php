<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPDATE
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'Update',
    'label' => 'POST ajax update invalide',
    'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $payload = http_build_query([
            'jacquette' => '99',
            'livre_note' => '-3',
            'commentaire' => str_repeat('x', 1500),
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

        $statusOk = in_array($response['status'], [200, 400, 404, 422], true);
        $jsonOk = is_array($json);

        $hasErrorSignal = false;

        if ($jsonOk)
        {
            $hasErrorSignal =
                !empty($json['errors'])
                || !empty($json['error'])
                || (isset($json['success']) && $json['success'] === false);
        }

        return [
            'ok' => $statusOk && $jsonOk && $hasErrorSignal,
            'message' => 'status ' . $response['status'] . ($jsonOk ? ' | json erreur reçu' : ' | réponse non json'),
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'Update',
    'label' => 'POST ajax update slug inexistant',
    'url' => $base . '/manga/ajax/update-note/slug-inexistant/999999',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'jacquette' => '5',
            'livre_note' => '5',
            'commentaire' => 'test safe',
        ]);

        $response = requestUrl(
            $base . '/manga/ajax/update-note/slug-inexistant/999999',
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);
        $statusOk = in_array($response['status'], [404, 400, 422], true);

        return [
            'ok' => $statusOk && (is_array($json) || trim($response['body']) !== ''),
            'message' => 'status ' . $response['status'] . ' | slug/numero invalides refusés',
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