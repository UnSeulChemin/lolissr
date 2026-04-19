<?php

declare(strict_types=1);

namespace App\Core\Http;

final class UploadedFile
{
    public static function exists(string $key): bool
    {
        return isset($_FILES[$key], $_FILES[$key]['error'])
            && (int) $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public static function name(string $key): ?string
    {
        if (!self::exists($key))
        {
            return null;
        }

        $name = $_FILES[$key]['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    public static function tmp(string $key): ?string
    {
        if (!self::exists($key))
        {
            return null;
        }

        $tmp = $_FILES[$key]['tmp_name'] ?? null;

        return is_string($tmp) && $tmp !== '' ? $tmp : null;
    }

    public static function error(string $key): ?int
    {
        if (!isset($_FILES[$key]['error']))
        {
            return null;
        }

        return (int) $_FILES[$key]['error'];
    }

    public static function size(string $key): ?int
    {
        if (!self::exists($key))
        {
            return null;
        }

        $size = $_FILES[$key]['size'] ?? null;

        if (is_int($size))
        {
            return $size;
        }

        if (is_string($size) && ctype_digit($size))
        {
            return (int) $size;
        }

        return null;
    }

    public static function extension(string $key): ?string
    {
        $name = self::name($key);

        if ($name === null)
        {
            return null;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $extension !== '' ? $extension : null;
    }

    public static function mimeType(string $key): ?string
    {
        $tmpName = self::tmp($key);

        if ($tmpName === null || !is_file($tmpName))
        {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false)
        {
            return null;
        }

        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        return is_string($mimeType) && $mimeType !== ''
            ? strtolower($mimeType)
            : null;
    }
}