<?php

declare(strict_types=1);

final class CssBundleBuilder
{
    public static function compile(string $directory): string
    {
        $entry = self::read($directory . '/app.css');
        $expanded = preg_replace_callback(
            '~@import\s+url\(([\x22\x27])\./([^\x22\x27]+)\1\);~',
            static function (array $match) use ($directory): string {
                $relative = $match[2];
                $root = realpath($directory);
                $path = realpath($directory . '/' . $relative);
                if ($root === false || $path === false || ! str_starts_with($path, $root . DIRECTORY_SEPARATOR))
                {
                    throw new RuntimeException('Invalid CSS import: ' . $relative);
                }
                $css = self::read($path);
                if (str_contains($css, '@import'))
                {
                    throw new RuntimeException('Declare nested imports in app.css: ' . $relative);
                }
                // Relative asset URLs are resolved from the bundle's directory.
                return preg_replace_callback(
                    '~url\(\s*([\x22\x27]?)([^\x22\x27()]+)\1\s*\)~',
                    static function (array $url) use ($relative): string {
                        $value = trim($url[2]);
                        if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|\#)~i', $value) === 1)
                        {
                            return $url[0];
                        }
                        return 'url(' . $url[1] . dirname($relative) . '/' . $value . $url[1] . ')';
                    },
                    $css
                ) ?? throw new RuntimeException('Cannot rewrite CSS URLs.');
            },
            $entry
        ) ?? throw new RuntimeException('Cannot expand CSS imports.');

        return self::compact($expanded) . "\n";
    }

    // Preserve quoted content and meaningful spaces, including calc() operators.
    public static function compact(string $css): string
    {
        $pattern = '~("(?:\\\\.|[^"\\\\])*"|\x27(?:\\\\.|[^\x27\\\\])*\x27)|/\*.*?\*/|(\s+)~s';
        $result = preg_replace_callback($pattern, static function (array $match): string {
            if (($match[1] ?? '') !== '') return $match[1];
            return isset($match[2]) ? ' ' : '';
        }, $css);
        return trim($result ?? throw new RuntimeException('Cannot compact CSS.'));
    }

    private static function read(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) throw new RuntimeException('Cannot read CSS: ' . $path);
        return $content;
    }
}
