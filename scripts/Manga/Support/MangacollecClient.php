<?php

declare(strict_types=1);

final class MangacollecClient
{
    private const MAX_COVER_BYTES = 5 * 1024 * 1024;
    private const MAX_JSON_BYTES = 16 * 1024 * 1024;

    private string $token;
    private array $responses = [];

    public function __construct()
    {
        if (!extension_loaded('curl')) throw new RuntimeException('PHP curl extension required.');
        $session = $this->request('/session/app', true);
        $token = $session['access_token'] ?? null;
        if (!is_string($token) || $token === '') throw new RuntimeException('Guest session unavailable.');
        $this->token = $token;
    }

    public function get(string $path): array
    {
        if (!isset($this->responses[$path]))
        {
            usleep(200000);
            $this->responses[$path] = $this->request('/v2' . $path);
        }
        return $this->responses[$path];
    }

    private function request(string $path, bool $session = false): array
    {
        $headers = ['Accept: application/json', 'Origin: https://www.mangacollec.com',
            'X-App-Version: 2.19.0', 'X-App-Build-Number: 120', 'X-System-Name: Web', 'X-System-Version: 1.0.0'];
        if (!$session) $headers[] = 'Authorization: Bearer ' . $this->token;
        [$body, $status, , $error] = $this->boundedRequest('https://api.mangacollec.com' . $path, $headers, $session, self::MAX_JSON_BYTES);
        // Windows curl uses the system trust store when PHP has no CA bundle configured.
        if ($status === 0 && PHP_OS_FAMILY === 'Windows' && str_contains($error, 'SSL certificate'))
        {
            [$body, $status] = $this->windowsRequest($path, $session, $headers, maxBytes: self::MAX_JSON_BYTES);
        }
        if (!is_string($body) || $status !== 200)
            throw new RuntimeException('Mangacollec HTTP ' . $status . ($error !== '' ? ' (' . $error . ')' : ''));
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) throw new RuntimeException('Invalid Mangacollec response.');
        return $data;
    }

    private function boundedRequest(string $url, array $headers, bool $post, int $maxBytes, int $timeout = 20): array
    {
        $body = '';
        $overflow = false;
        $handle = curl_init($url);
        try
        {
            curl_setopt_array($handle, [CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => $timeout,
                CURLOPT_HTTPHEADER => $headers, CURLOPT_POST => $post,
                CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body, &$overflow, $maxBytes): int
                {
                    $length = strlen($chunk);
                    if ($length > $maxBytes - strlen($body))
                    {
                        $overflow = true;
                        return 0;
                    }
                    $body .= $chunk;
                    return $length;
                }]);
            $success = curl_exec($handle);
            if ($overflow) throw new OverflowException('Mangacollec response exceeds ' . $maxBytes . ' bytes.');
            return [$success === false ? false : $body, curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
                curl_getinfo($handle, CURLINFO_REDIRECT_URL), curl_error($handle)];
        }
        finally
        { curl_close($handle); }
    }

    private function windowsRequest(string $path, bool $session, array $headers, ?string $url = null, int $maxBytes = self::MAX_COVER_BYTES): array
    {
        // Keep metadata separate so an exact-limit body is accepted without buffering an unbounded stdout.
        $metadataPath = tempnam(sys_get_temp_dir(), 'mangacollec-');
        if ($metadataPath === false) throw new RuntimeException('Cannot stage HTTPS response metadata.');
        $process = null;
        $pipes = [];
        try
        {
            $process = proc_open(['curl.exe', '--silent', '--show-error', '--connect-timeout', '5', '--max-time', '20',
                '--request', $session ? 'POST' : 'GET', '--config', '-', '--write-out', "%{stderr}\n%{redirect_url}\n%{http_code}",
                $url ?? 'https://api.mangacollec.com' . $path],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $metadataPath, 'w']], $pipes,
                ROOT, null, ['bypass_shell' => true]);
            if (!is_resource($process)) throw new RuntimeException('Windows HTTPS client unavailable.');
            foreach ($headers as $header)
            {
                $line = 'header = "' . addcslashes($header, "\\\"") . "\"\n";
                if (fwrite($pipes[0], $line) !== strlen($line)) throw new RuntimeException('Cannot configure Windows HTTPS request.');
            }
            fclose($pipes[0]);
            $body = stream_get_contents($pipes[1], $maxBytes + 1);
            if (is_string($body) && strlen($body) > $maxBytes)
                throw new OverflowException('Mangacollec response exceeds ' . $maxBytes . ' bytes.');
            fclose($pipes[1]);
            $exit = proc_close($process);
            $process = null;
            if ($exit !== 0 || !is_string($body)) throw new RuntimeException('Windows HTTPS request failed.');
            $metadata = file_get_contents($metadataPath, false, null, 0, 8193);
            if ($metadata === false || strlen($metadata) > 8192) throw new RuntimeException('Invalid HTTPS response metadata.');
            $separator = strrpos($metadata, "\n");
            if ($separator === false) throw new RuntimeException('Missing HTTPS response status.');
            $redirectSeparator = strrpos(substr($metadata, 0, $separator), "\n");
            if ($redirectSeparator === false) throw new RuntimeException('Missing HTTPS redirect metadata.');
            return [$body, (int) substr($metadata, $separator + 1), substr($metadata, $redirectSeparator + 1, $separator - $redirectSeparator - 1)];
        }
        finally
        {
            if (is_resource($process)) proc_terminate($process);
            foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
            if (is_resource($process)) proc_close($process);
            unlink($metadataPath);
        }
    }

    public function cover(string $id, ?string $url): void
    {
        if ($url === null || preg_match('/^[a-f0-9-]{36}$/D', $id) !== 1) return;
        if (!self::allowedCoverUrl($url)) return;
        $directory = ROOT . '/public/images/manga/upcoming';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new RuntimeException('Cannot create cover directory.');
        $path = $directory . '/' . $id . '.jpg';
        $checkedPath = $path . '.checked';
        $source = hash('sha256', $url);
        $checkedSource = is_file($checkedPath) ? @file_get_contents($checkedPath) : false;
        $checkedAt = is_file($checkedPath) ? filemtime($checkedPath) : (is_file($path) ? filemtime($path) : false);
        if (is_file($path) && $checkedSource === $source && $checkedAt !== false && time() - $checkedAt < 7 * 86400) return;
        for ($hop = 0; $hop < 4; $hop++)
        {
            if (!self::allowedCoverUrl($url)) return;
            try
            {
                if (PHP_OS_FAMILY === 'Windows')
                    [$body, $status, $redirect] = $this->windowsRequest('', false, [], $url);
                else
                    [$body, $status, $redirect] = $this->boundedRequest($url, [], false, self::MAX_COVER_BYTES, 15);
            }
            catch (OverflowException)
            { return; }
            if (!in_array($status, [301, 302, 303, 307, 308], true)) break;
            if (!is_string($redirect) || $redirect === '') return;
            $url = $redirect;
        }
        if ($status !== 200 || !is_string($body) || strlen($body) > self::MAX_COVER_BYTES) return;
        $size = @getimagesizefromstring($body);
        if ($size === false || $size[0] * $size[1] > 10000000) return;
        $image = @imagecreatefromstring($body);
        if ($image === false) return;
        $ratio = min(1, 320 / $size[0], 480 / $size[1]);
        $resized = imagescale($image, max(1, (int) ($size[0] * $ratio)), max(1, (int) ($size[1] * $ratio)));
        imagedestroy($image);
        if ($resized === false) return;
        ob_start();
        imagejpeg($resized, null, 82);
        $jpeg = ob_get_clean();
        imagedestroy($resized);
        if (is_string($jpeg))
        {
            AtomicFile::writeIfChanged($path, $jpeg);
            AtomicFile::writeIfChanged($checkedPath, $source);
            touch($checkedPath);
        }
    }

    public static function allowedCoverUrl(string $url): bool
    {
        $parts = parse_url($url);
        return is_array($parts) && ($parts['scheme'] ?? '') === 'https'
            && in_array($parts['host'] ?? '', ['m.media-amazon.com', 'images-eu.ssl-images-amazon.com', 'www.bdfugue.com', 'api.mangacollec.com', 'mangacollec.s3.eu-west-3.amazonaws.com'], true)
            && !isset($parts['user']) && !isset($parts['pass'])
            && (!isset($parts['port']) || $parts['port'] === 443);
    }
}
