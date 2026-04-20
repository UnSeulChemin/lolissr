<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS AJAX MUTATEURS
|--------------------------------------------------------------------------
*/

if (!isset($testAjaxUpdate))
{
    $testAjaxUpdate = false;
}

if ($testAjaxUpdate)
{
    addPostCheck($postChecks, [
        'category' => 'AJAX',
        'label' => 'POST ajax update note validation invalide',
        'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
            $payload = http_build_query([
                'jacquette' => '9',
                'livre_note' => 'abc',
                'commentaire' => str_repeat('x', 1205),
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

            $json = decodeJsonResponse($response['body']);

            $ok = $response['status'] === 422
                && is_array($json)
                && isset($json['success'])
                && $json['success'] === false
                && !empty($json['errors']);

            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'] . ' | validation AJAX invalide',
            ];
        },
    ]);

    addPostCheck($postChecks, [
        'category' => 'AJAX',
        'label' => 'POST ajax update note bloqué en mode test',
        'url' => $base . '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero,
        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
            $payload = http_build_query([
                'jacquette' => '4',
                'livre_note' => '5',
                'commentaire' => 'test ajax ok',
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

            $json = decodeJsonResponse($response['body']);

            $ok = $response['status'] === 403
                && is_array($json)
                && isset($json['success'])
                && $json['success'] === false
                && isset($json['message'])
                && is_string($json['message']);

            return [
                'ok' => $ok,
                'message' => 'status ' . $response['status'] . ' | update AJAX bloqué',
            ];
        },
    ]);
}