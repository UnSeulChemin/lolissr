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

echo "PASS: $classes PSR-4 declarations and $imports source imports.\n";
