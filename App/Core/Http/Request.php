<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    public function __construct(
        private readonly array $get = [],
        private readonly array $post = [],
        private readonly array $files = [],
        private readonly array $server = []
    ) {}

    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER
        );
    }

    public function all(): array
    {
        return [
            ...$this->get,
            ...$this->post,
        ];
    }

    public function input(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    public function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->get[$key]
            ?? $default;
    }

    public function post(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->post[$key] ?? $default;
    }

    public function postAll(): array
    {
        return $this->post;
    }

    public function string(
        string $key,
        string $default = ''
    ): string {
        return trim(
            (string) $this->input($key, $default)
        );
    }

    public function integer(
        string $key,
        int $default = 0
    ): int {
        return (int) $this->input($key, $default);
    }

    public function boolean(
        string $key,
        bool $default = false
    ): bool {
        $value = filter_var(
            $this->input($key, $default),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        return $value ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists(
            $key,
            $this->all()
        );
    }

    public function filled(string $key): bool
    {
        return trim(
            (string) $this->input($key)
        ) !== '';
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    public function except(array $keys): array
    {
        return array_diff_key(
            $this->all(),
            array_flip($keys)
        );
    }

    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file)
            ? $file
            : null;
    }

    public function hasFile(string $key): bool
    {
        $file = $this->file($key);

        if ($file === null) {
            return false;
        }

        return isset($file['tmp_name'])
            && $file['tmp_name'] !== '';
    }

    public function hasValidFile(string $key): bool
    {
        $file = $this->file($key);

        if ($file === null) {
            return false;
        }

        return ($file['error'] ?? UPLOAD_ERR_NO_FILE)
            === UPLOAD_ERR_OK;
    }

    public function method(): string
    {
        return strtoupper(
            trim(
                (string) (
                    $this->server['REQUEST_METHOD']
                    ?? 'GET'
                )
            )
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
            PHP_URL_PATH
        );

        if (!is_string($path) || $path === '') {
            return '/';
        }

        $basePath = rtrim(
            base_path(),
            '/'
        );

        if (
            $basePath !== ''
            && $basePath !== '/'
            && str_starts_with($path, $basePath)
        ) {
            $path = substr(
                $path,
                strlen($basePath)
            );
        }

        $path = trim($path, '/');

        return $path === ''
            ? '/'
            : '/' . $path;
    }

    public function server(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->server[$key]
            ?? $default;
    }

    public function isAjax(): bool
    {
        return strtolower(
            (string) $this->server(
                'HTTP_X_REQUESTED_WITH',
                ''
            )
        ) === 'xmlhttprequest';
    }
}