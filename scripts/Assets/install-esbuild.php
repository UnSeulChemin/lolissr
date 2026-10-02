<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../Support/JavaScriptBuilder.php';
$platform = match (PHP_OS_FAMILY) { 'Windows' => 'win32', 'Darwin' => 'darwin', 'Linux' => 'linux', default => throw new RuntimeException('Unsupported platform') };
$architecture = match (strtolower(php_uname('m'))) { 'amd64', 'x86_64' => 'x64', 'aarch64', 'arm64' => 'arm64', default => throw new RuntimeException('Unsupported architecture') };
$download = static function (string $url): string {
    // Windows curl uses the system certificate store, unlike Wamp's PHP cURL.
    if (PHP_OS_FAMILY === 'Windows')
    {
        $process = proc_open(['curl.exe', '--fail', '--silent', '--show-error', '--max-time', '90', $url],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => STDERR], $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($process)) throw new RuntimeException('Cannot start curl');
        fclose($pipes[0]);
        $body = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        if (proc_close($process) !== 0 || $body === false) throw new RuntimeException('Download failed');
        return $body;
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FAILONERROR => true, CURLOPT_TIMEOUT => 90]);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);
    if (!is_string($body)) throw new RuntimeException('Download failed: ' . $error);
    return $body;
};
$metadata = json_decode($download('https://registry.npmjs.org/@esbuild/' . $platform . '-' . $architecture . '/' . JavaScriptBuilder::VERSION), true, 512, JSON_THROW_ON_ERROR);
$url = $metadata['dist']['tarball'];
if (!str_starts_with($url, 'https://registry.npmjs.org/')) throw new RuntimeException('Unexpected package host');
$archiveBytes = $download($url);
$integrity = 'sha512-' . base64_encode(hash('sha512', $archiveBytes, true));
if (!hash_equals($metadata['dist']['integrity'], $integrity)) throw new RuntimeException('Package integrity mismatch');
$target = JavaScriptBuilder::binary(dirname(__DIR__, 2));
if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
$archive = dirname($target) . '/esbuild-download.tgz';
try
{
    file_put_contents($archive, $archiveBytes);
    $package = new PharData($archive);
    $executable = $package[$platform === 'win32' ? 'package/esbuild.exe' : 'package/bin/esbuild']->getContent();
    if (file_put_contents($target, $executable) === false) throw new RuntimeException('Cannot install esbuild');
    if ($platform !== 'win32') chmod($target, 0755);
    echo 'Installed esbuild ' . JavaScriptBuilder::VERSION . " locally.\n";
}
finally
{
    unset($package);
    if (is_file($archive)) unlink($archive);
}
