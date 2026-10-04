<?php
declare(strict_types=1);
namespace App\Support\Media;

final class JpegOrientation
{
    public static function read(string $bytes): ?int
    {
        for ($offset = 2; $offset + 4 <= strlen($bytes);)
        {
            if (ord($bytes[$offset]) !== 255) return null;
            $marker = ord($bytes[$offset + 1]);
            if ($marker === 218 || $marker === 217) return 1;
            $length = ord($bytes[$offset + 2]) * 256 + ord($bytes[$offset + 3]);
            if ($length < 2 || $offset + 2 + $length > strlen($bytes)) return null;
            if ($marker === 225 && substr($bytes, $offset + 4, 6) === "Exif\0\0")
            {
                $tiff = substr($bytes, $offset + 10, $length - 8);
                if (strlen($tiff) < 8 || !in_array(substr($tiff, 0, 2), ['II', 'MM'], true)) return null;
                $little = substr($tiff, 0, 2) === 'II';
                $read = static function (int $position, int $size) use ($tiff, $little): ?int
                {
                    if ($position < 0 || $position + $size > strlen($tiff)) return null;
                    $data = unpack($size === 2 ? ($little ? 'vvalue' : 'nvalue') : ($little ? 'Vvalue' : 'Nvalue'), substr($tiff, $position, $size));
                    return $data === false ? null : (int) $data['value'];
                };
                if ($read(2, 2) !== 42) return null;
                $ifd = $read(4, 4);
                if ($ifd === null) return null;
                $count = $read($ifd, 2);
                if ($count === null || $ifd + 2 + $count * 12 > strlen($tiff)) return null;
                for ($index = 0; $index < $count; $index++)
                {
                    $entry = $ifd + 2 + $index * 12;
                    if ($read($entry, 2) !== 274) continue;
                    if ($read($entry + 2, 2) !== 3 || $read($entry + 4, 4) !== 1) return null;
                    $orientation = $read($entry + 8, 2);
                    return $orientation !== null && $orientation >= 1 && $orientation <= 8 ? $orientation : null;
                }
            }
            $offset += $length + 2;
        }
        return 1;
    }
}
