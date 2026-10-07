<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__, 3));
require ROOT . '/scripts/Manga/Support/MangacollecClient.php';

// Real local transfers exercise both transports without external services or application data.
$directory = sys_get_temp_dir() . '/mangacollec-limit-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$listener = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
if ($listener === false) throw new RuntimeException($errorMessage);
$address = stream_socket_get_name($listener, false);
fclose($listener);
$router = <<<'PHP'
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/redirect')
{
    header('Location: https://m.media-amazon.com/fixture.jpg', true, 302);
    echo 'redirect';
    return;
}
if ($path === '/image')
{
    header('Content-Type: image/jpeg');
    $image = imagecreatetruecolor(2, 2);
    imagejpeg($image);
    imagedestroy($image);
    return;
}
if ($path === '/json')
{
    header('Content-Type: application/json');
    echo '{"access_token":"fixture"}';
    return;
}
$size = (int) ($_GET['size'] ?? 0);
if (isset($_GET['declared'])) header('Content-Length: ' . $size);
while ($size > 0)
{
    $length = min(8192, $size);
    echo str_repeat('a', $length);
    $size -= $length;
    flush();
}
PHP;
file_put_contents($directory . '/router.php', $router);
$process = proc_open([PHP_BINARY, '-S', $address, $directory . '/router.php'],
    [0 => ['pipe', 'r'], 1 => ['file', $directory . '/server.log', 'a'], 2 => ['file', $directory . '/server.log', 'a']], $pipes, $directory);
if (!is_resource($process)) throw new RuntimeException('Cannot start transfer fixture.');
fclose($pipes[0]);
$assert = static function (bool $condition, string $message): void
{ if (!$condition) throw new RuntimeException($message); };
try
{
    $deadline = microtime(true) + 5;
    do
    {
        $socket = @stream_socket_client('tcp://' . $address, $errorCode, $errorMessage, 0.1);
        if ($socket !== false)
        { fclose($socket); break; }
        usleep(20000);
    } while (microtime(true) < $deadline);
    $assert($socket !== false, 'Local transfer server unavailable.');
    $reflection = new ReflectionClass(MangacollecClient::class);
    $client = $reflection->newInstanceWithoutConstructor();
    $limits = [$reflection->getConstant('MAX_COVER_BYTES'), $reflection->getConstant('MAX_JSON_BYTES')];
    $transports = ['boundedRequest'];
    if (PHP_OS_FAMILY === 'Windows') $transports[] = 'windowsRequest';
    $base = 'http://' . $address;
    foreach ($transports as $transport)
    {
        $method = $reflection->getMethod($transport);
        $request = static fn (string $url, int $limit): array => $transport === 'boundedRequest'
            ? $method->invoke($client, $url, [], false, $limit)
            : $method->invoke($client, '', false, [], $url, $limit);
        $metadataBefore = glob(sys_get_temp_dir() . '/mangacollec-*');
        foreach ($limits as $limit)
        {
            foreach ([false, true] as $declared)
            {
                foreach ([$limit, $limit + 1, $limit * 2] as $size)
                {
                    $overflow = false;
                    try
                    {
                        [$body, $status] = $request($base . '/bytes?size=' . $size . ($declared ? '&declared=1' : ''), $limit);
                        $assert($size <= $limit && $status === 200 && strlen($body) === $size, 'Incorrect bounded transfer result.');
                        unset($body);
                    }
                    catch (OverflowException)
                    { $overflow = true; }
                    $assert($overflow === ($size > $limit), 'Incorrect overflow decision.');
                }
            }
        }
        [$body, $status] = $request($base . '/json', $limits[1]);
        $assert($status === 200 && json_decode($body, true)['access_token'] === 'fixture', 'Normal JSON corrupted.');
        [$body, $status] = $request($base . '/image', $limits[0]);
        $assert($status === 200 && getimagesizefromstring($body)[2] === IMAGETYPE_JPEG, 'Normal JPEG corrupted.');
        [$body, $status, $redirect] = $request($base . '/redirect', $limits[0]);
        $assert($status === 302 && $body === 'redirect' && MangacollecClient::allowedCoverUrl($redirect), 'Allowed redirect metadata lost or followed automatically.');
        $assert(!MangacollecClient::allowedCoverUrl('https://127.0.0.1/image'), 'Cover allowlist weakened.');
        $assert(glob(sys_get_temp_dir() . '/mangacollec-*') === $metadataBefore, 'Temporary response metadata leaked.');
        echo 'PASS: ' . $transport . ' exact limits, overflow with/without Content-Length, JSON, JPEG, redirect and cleanup.' . PHP_EOL;
    }
}
finally
{
    proc_terminate($process);
    proc_close($process);
    foreach (['router.php', 'server.log'] as $file) if (is_file($directory . '/' . $file)) unlink($directory . '/' . $file);
    rmdir($directory);
}
