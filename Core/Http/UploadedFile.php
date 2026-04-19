<?php

declare(strict_types=1);

namespace App\Core\Http;

final class UploadedFile
{
    /**
     * Vérifie si un fichier uploadé existe.
     */
    public static function exists(string $key): bool
    {
        return Request::hasFile($key)
            && Request::fileError($key) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Retourne le nom original du fichier.
     */
    public static function name(string $key): ?string
    {
        if (!self::exists($key))
        {
            return null;
        }

        $name = Request::fileName($key);

        return $name !== '' ? $name : null;
    }

    /**
     * Retourne le chemin temporaire du fichier.
     */
    public static function tmp(string $key): ?string
    {
        if (!self::exists($key))
        {
            return null;
        }

        $tmp = Request::fileTmpPath($key);

        return $tmp !== '' ? $tmp : null;
    }

    /**
     * Retourne le code d'erreur du fichier.
     */
    public static function error(string $key): ?int
    {
        if (!Request::hasFile($key))
        {
            return null;
        }

        return Request::fileError($key);
    }

    /**
     * Retourne la taille du fichier.
     */
    public static function size(string $key): ?int
    {
        if (!self::exists($key))
        {
            return null;
        }

        return Request::fileSize($key);
    }

    /**
     * Retourne l'extension du fichier.
     */
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

    /**
     * Retourne le type MIME réel du fichier.
     */
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