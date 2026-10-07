<?php
declare(strict_types=1);

// Isolated Apache instance: exercise the real .htaccess files at two mount paths.
$root = dirname(__DIR__, 3);
$binary = getenv('APACHE_TEST_BINARY') ?: (glob('C:/wamp64/bin/apache/apache*/bin/httpd.exe')[0] ?? '');
if (!is_file($binary)) throw new RuntimeException('Set APACHE_TEST_BINARY to a local Apache 2.4 executable.');
$serverRoot = str_replace('\\', '/', dirname($binary, 2));
$fixture = $root . '/storage/tools/apache-portability-' . bin2hex(random_bytes(8));
mkdir($fixture . '/site', 0700, true);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
if ($socket === false) throw new RuntimeException($errorMessage);
$address = stream_socket_get_name($socket, false);
$port = (int) substr($address, strrpos($address, ':') + 1);
fclose($socket);
$process = null;
try
{
    foreach (['', '/renamed-site'] as $mount)
    {
        $directory = $fixture . '/site' . $mount;
        mkdir($directory . '/public/js/dist/chunks', 0700, true);
        mkdir($directory . '/public/css', 0700, true);
        mkdir($directory . '/public/images/thumbnail', 0700, true);
        copy($root . '/.htaccess', $directory . '/.htaccess-portability');
        copy($root . '/public/.htaccess', $directory . '/public/.htaccess-portability');
        copy($root . '/public/js/dist/.htaccess', $directory . '/public/js/dist/.htaccess-portability');
        // A static marker front controller isolates rewriting from PHP/app configuration.
        file_put_contents($directory . '/public/index.php', 'FRONT_CONTROLLER');
        file_put_contents($directory . '/public/css/app.css', 'CSS_ASSET');
        file_put_contents($directory . '/public/js/dist/app-ABCDEFGH.js', 'JS_ASSET');
        file_put_contents($directory . '/public/js/dist/chunks/chunk-ABCDEFGH.js', 'CHUNK_ASSET');
    }
    $configuration = "ServerRoot \"$serverRoot\"\nListen 127.0.0.1:$port\nServerName localhost\n";
    foreach (['authz_core', 'mime', 'dir', 'autoindex', 'rewrite', 'headers'] as $module)
        $configuration .= "LoadModule {$module}_module modules/mod_$module.so\n";
    $configuration .= "AccessFileName .htaccess-portability\nTypesConfig \"$serverRoot/conf/mime.types\"\nDirectoryIndex index.php\n"
        . "PidFile \"$fixture/httpd.pid\"\nErrorLog \"$fixture/error.log\"\n"
        . "DocumentRoot \"$fixture/site\"\n<Directory \"$fixture/site\">\n"
        . "AllowOverride All\nOptions Indexes FollowSymLinks\nRequire all granted\n</Directory>\n"
        . "Header set X-Test-Query \"expr=%{QUERY_STRING}\"\n";
    file_put_contents($fixture . '/httpd.conf', $configuration);
    $process = proc_open([$binary, '-f', $fixture . '/httpd.conf', '-X'],
        [0 => ['pipe', 'r'], 1 => ['file', $fixture . '/stdout.log', 'a'], 2 => ['file', $fixture . '/stderr.log', 'a']], $pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot start isolated Apache.');
    fclose($pipes[0]);
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++)
    {
        $connection = @stream_socket_client("tcp://127.0.0.1:$port", $errorCode, $errorMessage, 0.1);
        if ($connection !== false)
        { fclose($connection); $ready = true; break; }
        usleep(100000);
    }
    if (!$ready) throw new RuntimeException('Apache did not start: ' . file_get_contents($fixture . '/stderr.log'));
    foreach (['', '/renamed-site'] as $mount)
    {
        foreach (['/' => 'FRONT_CONTROLLER', '/manga/series/example/1?q=fragment&numero=1' => 'FRONT_CONTROLLER',
            '/css/app.css?v=test' => 'CSS_ASSET', '/js/dist/app-ABCDEFGH.js' => 'JS_ASSET',
            '/js/dist/chunks/chunk-ABCDEFGH.js' => 'CHUNK_ASSET', '/public/css/app.css' => 'CSS_ASSET'] as $path => $expected)
        {
            $curl = curl_init("http://127.0.0.1:$port" . $mount . $path);
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 5]);
            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            curl_close($curl);
            if ($status !== 200 || !is_string($response) || substr($response, $headerSize) !== $expected)
                throw new RuntimeException('Wrong rewrite: ' . $mount . $path . ' status=' . $status . ' ' . file_get_contents($fixture . '/error.log'));
            if (str_contains($path, '?q=') && !str_contains($response, 'X-Test-Query: q=fragment&numero=1'))
                throw new RuntimeException('Query string lost');
            if (str_contains($path, '/js/dist/') && !str_contains($response, 'immutable'))
                throw new RuntimeException('Bundle caching lost');
            echo 'PASS: ' . ($mount ?: '(root)') . $path . PHP_EOL;
        }
        foreach (['/js/', '/js/dist/', '/js/dist/chunks/', '/css/', '/images/', '/images/thumbnail/'] as $path)
        {
            foreach ([$path, '/public' . $path] as $directoryPath)
            {
                $curl = curl_init("http://127.0.0.1:$port" . $mount . $directoryPath);
                curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 5]);
                $response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                if ($status !== 403 || !is_string($response) || str_contains($response, 'Index of'))
                    throw new RuntimeException('Directory listing must be denied: ' . $mount . $directoryPath . ' status=' . $status);
            }
        }
        echo 'PASS: directory listings denied at ' . ($mount ?: '(root)') . ' through both public URL forms.' . PHP_EOL;
    }
}
finally
{
    if (is_resource($process))
    { proc_terminate($process); proc_close($process); }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($fixture);
}
