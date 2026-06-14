<?php

declare(strict_types=1);

const HTTP_COOKIE_NAME = 'LOLISSR_SESSION';

$GLOBALS['http_cookie'] = '';

function http_config(): array
{
    /** @var array<string,mixed> */
    return require dirname(__DIR__)
        . '/http-config.php';
}

function http_base(): string
{
    return (string) (
        http_config()['base']
        ?? ''
    );
}

function http_timeout(): int
{
    return (int) (
        http_config()['timeout']
        ?? 10
    );
}

function http_cookie(): string
{
    return (string) (
        $GLOBALS['http_cookie']
        ?? ''
    );
}

function http_set_cookie(
    string $cookie,
): void {
    $GLOBALS['http_cookie'] =
        $cookie;
}

/**
 * @param list<string> $headers
 */
function http_extract_cookie(
    array $headers,
): void {

    foreach ($headers as $header)
    {
        if (
            preg_match(
                '/Set-Cookie:\s*'
                . HTTP_COOKIE_NAME
                . '=([^;]+)/i',
                $header,
                $matches,
            )
        ) {

            http_set_cookie(
                $matches[1],
            );

            return;
        }
    }
}

/**
 * @param list<string> $headers
 * @return array{
 *     status:int,
 *     body:string,
 *     headers:list<string>
 * }
 */
function http_request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
): array {

    $config =
        http_config();

    $cookie =
        http_cookie();

    if ($cookie !== '')
    {
        $headers[] =
            'Cookie: '
            . HTTP_COOKIE_NAME
            . '='
            . $cookie;
    }

    $context =
        stream_context_create([

            'http' => [

                'method' => strtoupper(
                    $method,
                ),

                'ignore_errors' => true,

                'timeout' => http_timeout(),

                'content' =>
                    $body ?? '',

                'header' => implode(
                    "\r\n",
                    array_merge(
                        [
                            'User-Agent: '
                            . (
                                $config['user_agent']
                                ?? 'LoliSSR-TestRunner'
                            ),
                        ],
                        $headers,
                    ),
                ),
            ],
        ]);

    $responseBody =
        @file_get_contents(
            $url,
            false,
            $context,
        );

    /** @var list<string> $responseHeaders */
    $responseHeaders =
        is_array($http_response_header ?? null)
            ? $http_response_header
            : [];

    http_extract_cookie(
        $responseHeaders,
    );

    $status = 0;

    if (
        isset($responseHeaders[0])
        && preg_match(
            '/\s(\d{3})\s/',
            $responseHeaders[0],
            $matches,
        )
    ) {
        $status =
            (int) $matches[1];
    }

    return [

        'status' =>
            $status,

        'body' =>
            is_string(
                $responseBody,
            )
                ? $responseBody
                : '',

        'headers' =>
            $responseHeaders,
    ];
}

/**
 * @param list<string> $headers
 * @return array{
 *     status:int,
 *     body:string,
 *     headers:list<string>
 * }
 */
function http_get(
    string $url,
    array $headers = [],
): array {

    return http_request(
        'GET',
        $url,
        $headers,
    );
}

/**
 * @param list<string> $headers
 * @return array{
 *     status:int,
 *     body:string,
 *     headers:list<string>
 * }
 */
function http_post(
    string $url,
    array $headers = [],
    ?string $body = null,
): array {

    return http_request(
        'POST',
        $url,
        $headers,
        $body,
    );
}

function http_extract_csrf(
    string $html,
): ?string {

    if (
        preg_match(
            '/name="csrf_token"\s+value="([^"]+)"/i',
            $html,
            $matches,
        )
    ) {
        return $matches[1];
    }

    return null;
}

function http_login(): void
{
    $config =
        http_config();

    $username =
        (string) (
            $config['username']
            ?? ''
        );

    $password =
        (string) (
            $config['password']
            ?? ''
        );

    if (
        $username === ''
        || $password === ''
    ) {
        throw new RuntimeException(
            'HTTP_TEST_USERNAME ou HTTP_TEST_PASSWORD manquant.',
        );
    }

    $csrf =
        http_extract_csrf(
            http_get(
                http_base()
                . '/connexion',
            )['body'],
        );

    if ($csrf === null)
    {
        throw new RuntimeException(
            'Token CSRF introuvable.',
        );
    }

    $payload =
        http_build_query([
            'username' => $username,
            'password' => $password,
            'csrf_token' => $csrf,
        ]);

    http_post(
        http_base()
        . '/connexion',
        [
            'Content-Type: application/x-www-form-urlencoded',
        ],
        $payload,
    );

    if (http_cookie() === '')
    {
        throw new RuntimeException(
            'Connexion HTTP impossible.',
        );
    }
}