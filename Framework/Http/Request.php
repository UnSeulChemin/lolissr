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

        $serverKey =
            'HTTP_'
            . strtoupper(
                str_replace(
                    '-',
                    '_',
                    $key,
                ),
            );

        return $this->server[$serverKey]
            ?? null;
    }

    public function input(
        string $key,
        mixed $default = null,
    ): mixed {

        return $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    public function all(): array
    {
        return array_merge(
            $this->get,
            $this->post,
        );
    }

    public function postAll(): array
    {
        return $this->post;
    }

    public function queryAll(): array
    {
        return $this->get;
    }

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