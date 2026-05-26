<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Application\App;

final class Request
{
    private array $get;

    private array $post;

    private array $files;

    private array $server;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $json = null;

    public function __construct(
        array $get = [],
        array $post = [],
        array $files = [],
        array $server = [],
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->server = $server;
    }

    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
        );
    }

    public function method(): string
    {
        return strtoupper(
            $this->server['REQUEST_METHOD']
            ?? 'GET',
        );
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isAjax(): bool
    {
        return strtolower(
            $this->header(
                'X-Requested-With',
            ) ?? '',
        ) === 'xmlhttprequest';
    }

    public function isPrefetch(): bool
    {
        return
            strtolower(
                $this->header(
                    'Purpose',
                ) ?? '',
            ) === 'prefetch'
            || strtolower(
                $this->header(
                    'X-Prefetch',
                ) ?? '',
            ) === 'true';
    }

    public function wantsPartial(): bool
    {
        return strtolower(
            $this->header(
                'X-Partial',
            ) ?? '',
        ) === 'true';
    }

    public function expectsJson(): bool
    {
        return $this->isAjax()
            || str_contains(
                strtolower(
                    $this->header(
                        'Accept',
                    ) ?? '',
                ),
                'application/json',
            );
    }

    public function uri(): string
    {
        return (string) (
            $this->server['REQUEST_URI']
            ?? '/'
        );
    }

    public function path(): string
    {
        $path = parse_url(
            $this->uri(),
            PHP_URL_PATH,
        ) ?: '/';

        $baseUri = rtrim(
            App::baseUri(),
            '/',
        );

        if (
            $baseUri !== ''
            && $baseUri !== '/'
            && str_starts_with(
                $path,
                $baseUri,
            )
        ) {

            $path = substr(
                $path,
                strlen($baseUri),
            );
        }

        $path = trim(
            $path,
            '/',
        );

        return $path === ''
            ? '/'
            : '/' . $path;
    }

    /**
     * @return array<string, mixed>|mixed
     */
    public function server(
        ?string $key = null,
        mixed $default = null,
    ): mixed {

        if ($key === null) {
            return $this->server;
        }

        return $this->server[$key]
            ?? $default;
    }

    public function header(
        string $key,
    ): ?string {

        $normalized =
            strtoupper(
                str_replace(
                    '-',
                    '_',
                    $key,
                ),
            );

        return $this->server[
            'HTTP_' . $normalized
        ]
            ?? $this->server[
                $normalized
            ]
            ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $raw =
            file_get_contents(
                'php://input',
            );

        if (
            !is_string($raw)
            || trim($raw) === ''
        ) {

            return $this->json = [];
        }

        $decoded =
            json_decode(
                $raw,
                true,
            );

        return $this->json =
            is_array($decoded)
                ? $decoded
                : [];
    }

    public function input(
        string $key,
        mixed $default = null,
    ): mixed {

        $json =
            $this->json();

        return $json[$key]
            ?? $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge(
            $this->get,
            $this->post,
            $this->json(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function postAll(): array
    {
        return array_merge(
            $this->post,
            $this->json(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function queryAll(): array
    {
        return $this->get;
    }

    /**
     * @return array<string, mixed>
     */
    public function files(): array
    {
        return $this->files;
    }

    public function file(
        string $key,
    ): mixed {

        return $this->files[$key]
            ?? null;
    }
}