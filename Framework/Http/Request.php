<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Application\App;

final class Request
{
    /**
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function __construct(
        private readonly array $get = [],
        private readonly array $post = [],
        private readonly array $files = [],
        private readonly array $server = [],
    ) {
    }

    /**
     * Retourne une valeur des headers HTTP.
     */
    public function header(
        string $key,
        mixed $default = null,
    ): mixed {
        $serverKey = 'HTTP_' . strtoupper(
            str_replace('-', '_', $key),
        );

        return $this->server[$serverKey]
            ?? $default;
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

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            ...$this->get,
            ...$this->post,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function postAll(): array
    {
        return $this->post;
    }

    /**
     * @return array<string, mixed>
     */
    public function files(): array
    {
        return $this->files;
    }

    public function input(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    public function query(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->get[$key]
            ?? $default;
    }

    public function post(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->post[$key]
            ?? $default;
    }

    public function string(
        string $key,
        string $default = '',
    ): string {
        return trim(
            (string) $this->input(
                $key,
                $default,
            ),
        );
    }

    public function integer(
        string $key,
        int $default = 0,
    ): int {
        $value = $this->input($key);

        return is_numeric($value)
            ? (int) $value
            : $default;
    }

    public function boolean(
        string $key,
        bool $default = false,
    ): bool {
        $value = filter_var(
            $this->input(
                $key,
                $default,
            ),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );

        return $value ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists(
            $key,
            $this->all(),
        );
    }

    public function filled(string $key): bool
    {
        return $this->string($key) !== '';
    }

    /**
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    /**
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        return array_diff_key(
            $this->all(),
            array_flip($keys),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key]
            ?? null;

        return is_array($file)
            ? $file
            : null;
    }

    /**
     * Vérifie si un fichier existe.
     */
    public function hasFile(string $key): bool
    {
        return $this->file($key) !== null
            && $this->fileError($key)
                !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Vérifie si un fichier uploadé est valide.
     */
    public function hasValidFile(string $key): bool
    {
        return $this->fileError($key)
            === UPLOAD_ERR_OK;
    }

    /**
     * Retourne le nom original du fichier.
     */
    public function fileName(string $key): string
    {
        $file = $this->file($key);

        return isset($file['name'])
            ? (string) $file['name']
            : '';
    }

    /**
     * Retourne le chemin temporaire du fichier.
     */
    public function fileTmpPath(string $key): string
    {
        $file = $this->file($key);

        return isset($file['tmp_name'])
            ? (string) $file['tmp_name']
            : '';
    }

    /**
     * Retourne le code d'erreur du fichier.
     */
    public function fileError(string $key): int
    {
        $file = $this->file($key);

        return isset($file['error'])
            ? (int) $file['error']
            : UPLOAD_ERR_NO_FILE;
    }

    /**
     * Retourne la taille du fichier.
     */
    public function fileSize(string $key): int
    {
        $file = $this->file($key);

        return isset($file['size'])
            ? (int) $file['size']
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */

    public function method(): string
    {
        return strtoupper(
            trim(
                (string) (
                    $this->server['REQUEST_METHOD']
                    ?? 'GET'
                ),
            ),
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
            PHP_URL_PATH,
        );

        if (
            !is_string($path)
            || $path === ''
        ) {
            return '/';
        }

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

        $path = trim($path, '/');

        return $path === ''
            ? '/'
            : '/' . $path;
    }

    public function server(
        string $key,
        mixed $default = null,
    ): mixed {
        return $this->server[$key]
            ?? $default;
    }

    public function userAgent(): string
    {
        return (string) $this->server(
            'HTTP_USER_AGENT',
            '',
        );
    }

    public function isAjax(): bool
    {
        return strtolower(
            (string) $this->server(
                'HTTP_X_REQUESTED_WITH',
                '',
            ),
        ) === 'xmlhttprequest';
    }
}