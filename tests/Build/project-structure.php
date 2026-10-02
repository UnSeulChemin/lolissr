<?php

declare(strict_types=1);

require dirname(__DIR__) . '/Support/bootstrap.php';

$root = dirname(__DIR__, 2);
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

$documents = [$root . '/README.md', $root . '/App/README.md', $root . '/Framework/README.md',
    $root . '/Config/README.md', $root . '/scripts/README.md', $root . '/public/README.md', $root . '/tests/README.md',
    ...glob($root . '/docs/*.md')];
foreach ($documents as $document)
{
    preg_match_all('~\]\(([^)\s]+)\)~', (string) file_get_contents($document), $matches);
    foreach ($matches[1] as $link)
    {
        if (preg_match('~^(?:[a-z]+:|#|/)~i', $link)) continue;
        $path = explode('#', $link, 2)[0];
        if (!file_exists(dirname($document) . '/' . $path)) throw new RuntimeException('Broken documentation link: ' . $document . ' -> ' . $link);
    }
}
echo "PASS: $classes PSR-4 declarations, $imports source imports and local documentation links.\n";
