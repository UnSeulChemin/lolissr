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