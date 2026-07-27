<?php

declare(strict_types=1);

const HTTP_COOKIE_NAME = 'LOLISSR_SESSION';

$GLOBALS['http_cookie'] = '';

/**
 * @return array<string, mixed>
 */
function http_config(): array
{
    return require dirname(__DIR__) . '/http-config.php';
}

function http_base(): string
{
    return rtrim((string) (http_config()['base'] ?? ''), '/');
}

function http_timeout(): int
{
    return max(1, (int) (http_config()['timeout'] ?? 10));
}

function http_cookie(): string
{
    return (string) ($GLOBALS['http_cookie'] ?? '');
}

function http_set_cookie(string $cookie): void
{
    $GLOBALS['http_cookie'] = $cookie;
}

/**
 * @param list<string> $headers
 */
function http_extract_cookie(array $headers): void
{
    foreach ($headers as $header)
    {
        if (
            preg_match(
                '/^Set-Cookie:\s*' . preg_quote(HTTP_COOKIE_NAME, '/') . '=([^;]+)/i',
                $header,
                $matches
            ) !== 1
        ) {
            continue;
        }

        http_set_cookie($matches[1]);

        return;
    }
}

/**
 * @param list<string> $headers
 */
function http_header_value(array $headers, string $name): ?string
{
    $prefix = strtolower(trim($name)) . ':';

    foreach ($headers as $header)
    {
        if (! str_starts_with(strtolower($header), $prefix))
        {
            continue;
        }

        return trim(substr($header, strlen($prefix)));
    }

    return null;
}

function http_location_path(string $location): string
{
    $path = parse_url($location, PHP_URL_PATH);

    if (! is_string($path) || $path === '')
    {
        return '/';
    }

    return '/' . trim($path, '/') . '/';
}

/**
 * @param list<string> $headers
 *
 * @return array{
 *     status: int,
 *     body: string,
 *     headers: list<string>
 * }
 */
function http_request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null
): array {
    $config = http_config();
    $cookie = http_cookie();

    if ($cookie !== '')
    {
        $headers[] = 'Cookie: ' . HTTP_COOKIE_NAME . '=' . $cookie;
    }

    $requestHeaders = array_merge(
        [
            'User-Agent: ' . ($config['user_agent'] ?? 'LoliSSR-TestRunner'),
        ],
        $headers
    );

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
            'timeout' => http_timeout(),
            'content' => $body ?? '',
            'header' => implode("\r\n", $requestHeaders),
        ],
    ]);

    $responseBody = @file_get_contents($url, false, $context);

    /** @var list<string> $responseHeaders */
    $responseHeaders = is_array($http_response_header ?? null)
        ? $http_response_header
        : [];

    http_extract_cookie($responseHeaders);

    $status = 0;

    if (
        isset($responseHeaders[0])
        && preg_match('/\s(\d{3})(?:\s|$)/', $responseHeaders[0], $matches) === 1
    ) {
        $status = (int) $matches[1];
    }

    return [
        'status' => $status,
        'body' => is_string($responseBody) ? $responseBody : '',
        'headers' => $responseHeaders,
    ];
}

/**
 * @param list<string> $headers
 *
 * @return array{
 *     status: int,
 *     body: string,
 *     headers: list<string>
 * }
 */
function http_get(string $url, array $headers = []): array
{
    return http_request('GET', $url, $headers);
}

/**
 * @param list<string> $headers
 *
 * @return array{
 *     status: int,
 *     body: string,
 *     headers: list<string>
 * }
 */
function http_post(string $url, array $headers = [], ?string $body = null): array
{
    return http_request('POST', $url, $headers, $body);
}

function http_extract_csrf(string $html): ?string
{
    if (
        preg_match(
            '/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i',
            $html,
            $matches
        ) !== 1
    ) {
        return null;
    }

    return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function http_login(): void
{
    $config = http_config();

    $username = trim((string) ($config['username'] ?? ''));
    $password = (string) ($config['password'] ?? '');

    if ($username === '' || $password === '')
    {
        throw new RuntimeException(
            'HTTP_TEST_USERNAME ou HTTP_TEST_PASSWORD manquant.'
        );
    }

    $loginResponse = http_get(http_base() . '/connexion');

    if ($loginResponse['status'] !== 200)
    {
        throw new RuntimeException(
            'Page de connexion inaccessible. Statut HTTP reçu : '
            . $loginResponse['status']
        );
    }

    $csrf = http_extract_csrf($loginResponse['body']);

    if ($csrf === null)
    {
        throw new RuntimeException(
            'Token CSRF introuvable sur la page de connexion.'
        );
    }

    $payload = http_build_query([
        'username' => $username,
        'password' => $password,
        'csrf_token' => $csrf,
    ]);

    $authenticationResponse = http_post(
        http_base() . '/connexion',
        [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($payload),
        ],
        $payload
    );

    if (! in_array($authenticationResponse['status'], [302, 303], true))
    {
        throw new RuntimeException(
            sprintf(
                "La connexion HTTP a échoué. Statut reçu : %d.\nRéponse : %s",
                $authenticationResponse['status'],
                trim($authenticationResponse['body'])
            )
        );
    }

    $location = http_header_value(
        $authenticationResponse['headers'],
        'Location'
    );

    if ($location === null)
    {
        throw new RuntimeException(
            "La connexion n’a retourné aucun header Location.\nHeaders : "
            . implode("\n", $authenticationResponse['headers'])
        );
    }

    $expectedPath = http_location_path(http_base() . '/');
    $actualPath = http_location_path($location);

    if ($actualPath !== $expectedPath)
    {
        throw new RuntimeException(
            sprintf(
                'La connexion a redirigé vers "%s" au lieu de "%s".',
                $location,
                $expectedPath
            )
        );
    }

    if (http_cookie() === '')
    {
        throw new RuntimeException(
            'Cookie de session introuvable après connexion.'
        );
    }

    $profileResponse = http_get(http_base() . '/profil');

    if ($profileResponse['status'] !== 200)
    {
        throw new RuntimeException(
            'La session de test n’est pas authentifiée. Statut reçu : '
            . $profileResponse['status']
        );
    }
}