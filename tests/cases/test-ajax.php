<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS AJAX
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajouter validation AJAX invalide',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'livre' => '',
            'slug' => '',
            'numero' => '0',
            'commentaire' => str_repeat('x', 1205),
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
            'ok' => $response['status'] === 422 && $jsonOk && $hasErrorSignal,
            'message' => 'status ' . $response['status'] . ($jsonOk ? ' | json erreur reçu' : ' | réponse non json'),
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajouter body JSON non supporté',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $response = requestUrl(
            $base . '/manga/ajouter',
            'POST',
            [
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
            ],
            '{"livre":'
        );

        $json = json_decode($response['body'], true);

        $ok = $response['status'] === 422
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | body JSON non supporté',
        ];
    },
]);

addHtmlCheck($htmlChecks, [
    'category' => 'AJAX',
    'label' => 'GET détail contient hooks AJAX notes',
    'url' => $base . '/manga/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl($base . '/manga/' . $realSlug . '/' . $realNumero);
        $body = $response['body'];

        $hasHook = stripos($body, 'ajax-note') !== false
            || stripos($body, 'ajax-note-button') !== false
            || stripos($body, 'js-detail-card') !== false;

        return [
            'ok' => $response['status'] === 200 && $hasHook,
            'message' => $response['status'] === 200
                ? ($hasHook ? 'hooks AJAX trouvés' : 'hooks AJAX absents')
                : 'page détail inaccessible',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'GET ajax update note refusé en 405',
    'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $realSlug, $realNumero): array
    {
        $response = requestUrl(
            $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
            'GET',
            [
                'X-Requested-With: XMLHttpRequest',
            ]
        );

        $json = json_decode($response['body'], true);

        $ok = $response['status'] === 405
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | GET refusé',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST ajax update note manga introuvable',
    'url' => $base . '/manga/ajax/update-note/slug-introuvable-test/999',
    'callback' => static function () use ($base): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
        ]);

        $response = requestUrl(
            $base . '/manga/ajax/update-note/slug-introuvable-test/999',
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);

        $ok = $response['status'] === 404
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === false;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | manga introuvable',
        ];
    },
]);

addPostCheck($postChecks, [
    'category' => 'AJAX',
    'label' => 'POST update AJAX URL non canonique',
    'url' => $base . '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,
    'callback' => static function () use ($base, $nonCanonicalSlug, $realNumero): array
    {
        $payload = http_build_query([
            'jacquette' => '4',
            'livre_note' => '4',
            'commentaire' => 'test canonical ajax',
        ]);

        $response = requestUrl(
            $base . '/manga/update/' . $nonCanonicalSlug . '/' . $realNumero,
            'POST',
            [
                'Content-Type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest',
            ],
            $payload
        );

        $json = json_decode($response['body'], true);

        $ok = $response['status'] === 409
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === false
            && !empty($json['redirect']);

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'] . ' | URL non canonique',
        ];
    },
]);