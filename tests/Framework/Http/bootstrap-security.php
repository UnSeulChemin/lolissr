<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$directory = sys_get_temp_dir() . '/bootstrap-security-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$process = null;
try
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if ($socket === false) throw new RuntimeException($errorMessage);
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    if ($address === false) throw new RuntimeException('Cannot resolve fixture address.');
    $router = '<?php define("ROOT", ' . var_export($directory, true) . ');'
        . 'require ' . var_export($root . '/vendor/autoload.php', true) . ';'
        . 'require ' . var_export($root . '/Framework/Support/Helpers.php', true) . ';'
        . 'ini_set("error_log", ROOT . "/error.log");'
        . '\Framework\Application\Bootstrap::run();';
    file_put_contents($directory . '/router.php', $router);
    $process = proc_open([PHP_BINARY, '-S', $address, $directory . '/router.php'],
        [0 => ['pipe', 'r'], 1 => ['file', $directory . '/stdout.log', 'a'], 2 => ['file', $directory . '/stderr.log', 'a']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start bootstrap fixture.');
    fclose($pipes[0]);
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++)
    {
        $connection = @stream_socket_client('tcp://' . $address, $errorCode, $errorMessage, 0.1);
        if ($connection !== false)
        { fclose($connection); $ready = true; break; }
        usleep(20000);
    }
    if (!$ready) throw new RuntimeException('Bootstrap fixture did not start.');
    $ids = [];
    foreach (["INVALID_DECLARATION\n", "APP_ENV=invalid\n", ''] as $environment)
    {
        file_put_contents($directory . '/.env', $environment);
        $headers = [];
        $curl = curl_init('http://' . $address . '/');
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int
            {
                if (str_contains($line, ':'))
                {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            }]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if ($status !== 500 || $body !== 'Une erreur interne est survenue.')
            throw new RuntimeException('Bootstrap failure exposed details or changed status.');
        foreach (['x-content-type-options' => 'nosniff', 'x-frame-options' => 'DENY', 'referrer-policy' => 'no-referrer',
            'permissions-policy' => 'camera=(), microphone=(), geolocation=()',
            'content-security-policy' => "default-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'none';"] as $name => $value)
            if (($headers[$name] ?? null) !== $value) throw new RuntimeException('Missing bootstrap protection: ' . $name);
        $id = $headers['x-request-id'] ?? '';
        if (!preg_match('/^[a-f0-9]{16}$/D', $id) || in_array($id, $ids, true))
            throw new RuntimeException('Missing or reused bootstrap request ID.');
        $ids[] = $id;
        if (isset($headers['set-cookie']) || isset($headers['x-powered-by']) || is_dir($directory . '/storage/sessions'))
            throw new RuntimeException('Bootstrap failure opened a session or exposed PHP.');
    }
    echo "PASS: configuration failures keep baseline security headers, fresh request IDs, generic 500 and no session.\n";
}
finally
{
    if (is_resource($process))
    { proc_terminate($process); proc_close($process); }
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    if (is_file($directory . '/.env')) unlink($directory . '/.env');
    rmdir($directory);
}
