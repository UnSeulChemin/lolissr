<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/Support/bootstrap.php';

$root = dirname(__DIR__, 3);
$classes = 0;
foreach (['App', 'Framework'] as $directory)
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS)) as $file)
    {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $source = (string) file_get_contents($file->getPathname());
        if (!preg_match('/^namespace ([^;]+);/m', $source, $namespace)) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $class = str_replace('/', '\\', substr($relative, 0, -4));
        if ($namespace[1] !== str_replace('/', '\\', dirname($relative))
            || (!class_exists($class) && !interface_exists($class) && !trait_exists($class)))
            throw new RuntimeException('PSR-4 mismatch: ' . $relative);
        if (realpath((string) (new ReflectionClass($class))->getFileName()) !== realpath($file->getPathname()))
            throw new RuntimeException('Autoload resolves to another file: ' . $class);
        $classes++;
    }
}

$imports = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/public/js', FilesystemIterator::SKIP_DOTS)) as $file)
{
    if (!$file->isFile() || $file->getExtension() !== 'js'
        || str_contains(str_replace('\\', '/', $file->getPathname()), '/js/dist/')) continue;
    preg_match_all('~(?:\bfrom\s*|\bimport\s*\(\s*|\bimport\s*)[\'\"](\.[^\'\"]+\.js)[\'\"]~',
        (string) file_get_contents($file->getPathname()), $matches);
    foreach ($matches[1] as $import)
    {
        if (!is_file($file->getPath() . '/' . $import)) throw new RuntimeException('Missing module: ' . $file->getPathname() . ' -> ' . $import);
        $imports++;
    }
}

if (file_exists($root . '/.git'))
{
    $output = tmpfile();
    $errors = tmpfile();
    if ($output === false || $errors === false) throw new RuntimeException('Cannot capture tracked files.');
    try
    {
        $process = proc_open(['git', 'ls-files', '-z', '--', 'storage'],
            [0 => ['pipe', 'r'], 1 => $output, 2 => $errors], $pipes, $root, null, ['bypass_shell' => true]);
        if (!is_resource($process)) throw new RuntimeException('Cannot inspect tracked runtime files.');
        fclose($pipes[0]);
        if (proc_close($process) !== 0) throw new RuntimeException('Git tracked-file inspection failed.');
        rewind($output);
        $tracked = stream_get_contents($output);
        if ($tracked === false) throw new RuntimeException('Cannot read tracked files.');
        foreach (explode("\0", $tracked) as $path)
        {
            if (preg_match('~^storage/(?:admin-jobs|cache|logs|sessions|backups|bootstrap)/~', $path)
                && !preg_match('~^storage/(?:admin-jobs|cache|logs|sessions|backups)/\.gitkeep$~D', $path))
                throw new RuntimeException('Runtime file must not be tracked: ' . $path);
        }
        echo "PASS: runtime files excluded from the Git index.\n";
    }
    finally
    {
        fclose($output);
        fclose($errors);
    }
}

echo "PASS: $classes PSR-4 declarations and $imports source imports.\n";
