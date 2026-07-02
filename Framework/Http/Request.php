<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Application\App;

final class Request
{
    /**
     * @var array<string, mixed>
     */
    private array $get;

    /**
     * @var array<string, mixed>
     */
    private array $post;

    /**
     * @var array<string, mixed>
     */
    private array $files;

    /**
     * @var array<string, mixed>
     */
    private array $server;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $json = null;

    /**
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
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

    // =========================================
    // REQUÊTE
    // =========================================

    public static function capture(): self
    {
        /** @var array<string, mixed> $get */
        $get = $_GET;

        /** @var array<string, mixed> $post */
        $post = $_POST;

        /** @var array<string, mixed> $files */
        $files = $_FILES;

        /** @var array<string, mixed> $server */
        $server = $_SERVER;

        return new self($get, $post, $files, $server);
    }

    // =========================================
    // MÉTHODE
    // =========================================

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    // =========================================
    // EN-TÊTES
    // =========================================

    public function isAjax(): bool
    {
        return $this->headerLower('X-Requested-With') === 'xmlhttprequest'
            || $this->headerLower('X-Ajax') === 'true';
    }

    public function isPrefetch(): bool
    {
        return $this->headerLower('Purpose') === 'prefetch'
            || $this->headerLower('X-Prefetch') === 'true';
    }

    public function wantsPartial(): bool
    {
        return $this->headerLower('X-Partial') === 'true';
    }

    public function expectsJson(): bool
    {
        return $this->isAjax()
            || str_contains($this->headerLower('Accept'), 'application/json');
    }

    public function header(string $key): ?string
    {
        $normalized = strtoupper(str_replace('-', '_', $key));

        $value = $this->server['HTTP_' . $normalized] ?? $this->server[$normalized] ?? null;

        return is_string($value) ? $value : null;
    }

    // =========================================
    // URI
    // =========================================

    public function uri(): string
    {
        return (string) ($this->server['REQUEST_URI'] ?? '/');
    }

    public function path(): string
    {
        $path = parse_url($this->uri(), PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        $baseUri = rtrim(App::baseUri(), '/');

        if ($baseUri !== '' && $baseUri !== '/' && str_starts_with($path, $baseUri))
        {
            $path = substr($path, strlen($baseUri));
        }

        $path = trim($path, '/');

        return $path === '' ? '/' : '/' . $path;
    }

    // =========================================
    // SERVEUR
    // =========================================

    /**
     * @return array<string, mixed>|mixed
     */
    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null)
        {
            return $this->server;
        }

        return $this->server[$key] ?? $default;
    }

    // =========================================
    // DONNÉES
    // =========================================

    public function input(string $key, mixed $default = null): mixed
    {
        $json = $this->json();

        return $json[$key] ?? $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    public function filled(string $key): bool
    {
        return trim((string) $this->input($key, '')) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json());
    }

    /**
     * @return array<string, mixed>
     */
    public function postAll(): array
    {
        return array_merge($this->post, $this->json());
    }

    /**
     * @return array<string, mixed>
     */
    public function queryAll(): array
    {
        return $this->get;
    }

    // =========================================
    // FICHIERS
    // =========================================

    /**
     * @return array<string, mixed>
     */
    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    // =========================================
    // UTILITAIRES
    // =========================================

    /**
     * @return array<string, mixed>
     */
    private function json(): array
    {
        if ($this->json !== null)
        {
            return $this->json;
        }

        $raw = file_get_contents('php://input');

        if (! is_string($raw) || trim($raw) === '')
        {
            return $this->json = [];
        }

        $decoded = json_decode($raw, true);

        return $this->json = is_array($decoded) ? $decoded : [];
    }

    private function headerLower(string $key): string
    {
        return strtolower($this->header($key) ?? '');
    }
}
