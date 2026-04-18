<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SECURITY TESTS
|--------------------------------------------------------------------------
*/

if (!isset($testAjaxUpdate))
{
    $testAjaxUpdate = false;
}

if ($testAjaxUpdate)
{
    addPostCheck($postChecks, [
        'category' => 'Security',
        'label' => 'Injection HTML commentaire',
        'url' => buildTestUrl($base, '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero),
        'callback' => static function () use ($base, $realSlug, $realNumero): array
        {
            $payload = http_build_query([
                'jacquette' => '4',
                'livre_note' => '4',
                'commentaire' => '<script>alert(1)</script>',
            ]);

            $updateResponse = requestUrl(
                buildTestUrl($base, '/manga/ajax/update-note/' . $realSlug . '/' . $realNumero),
                'POST',
                [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Requested-With: XMLHttpRequest',
                ],
                $payload
            );

            $updateJson = decodeJsonResponse($updateResponse['body']);

            if ($updateResponse['status'] === 422)
            {
                return [
                    'ok' => is_array($updateJson) && ($updateJson['success'] ?? null) === false,
                    'message' => 'HTML dangereux refusé en validation',
                ];
            }

            if ($updateResponse['status'] !== 200)
            {
                return [
                    'ok' => false,
                    'message' => 'status inattendu ' . $updateResponse['status'],
                ];
            }

            $detailResponse = requestUrl(
                buildTestUrl($base, '/manga/' . $realSlug . '/' . $realNumero)
            );

            $body = $detailResponse['body'];

            $hasRawScript = stripos($body, '<script>alert(1)</script>') !== false;
            $hasEscapedScript =
                stripos($body, '&lt;script&gt;alert(1)&lt;/script&gt;') !== false
                || stripos($body, '&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt;') !== false;

            return [
                'ok' => $detailResponse['status'] === 200 && !$hasRawScript,
                'message' => $detailResponse['status'] === 200
                    ? ($hasEscapedScript ? 'HTML échappé correctement' : 'script brut absent')
                    : 'page détail inaccessible après update',
            ];
        },
    ]);
}