<?php

declare(strict_types=1);

final class ProfileImageBuilder
{
    /** Return false for animations or an unhelpful conversion. */
    public static function prepare(string $source, string $target, int $maxEdge): bool
    {
        $bytes = file_get_contents($source);
        if ($bytes === false) throw new RuntimeException('Cannot read ' . $source);
        $info = getimagesize($source);
        if ($info === false) throw new RuntimeException('Invalid image: ' . $source);
        if ($info[2] === IMAGETYPE_PNG)
        {
            for ($offset = 8; $offset + 12 <= strlen($bytes);)
            {
                $chunk = unpack('Nlength', substr($bytes, $offset, 4));
                if ($chunk === false) throw new RuntimeException('Invalid PNG chunk');
                if (substr($bytes, $offset + 4, 4) === 'acTL') return false;
                $offset += 12 + $chunk['length'];
            }
        }
        elseif ($info[2] === IMAGETYPE_WEBP)
        {
            for ($offset = 12; $offset + 8 <= strlen($bytes);)
            {
                $chunk = unpack('Vlength', substr($bytes, $offset + 4, 4));
                if ($chunk === false) throw new RuntimeException('Invalid WebP chunk');
                if (substr($bytes, $offset, 4) === 'ANIM') return false;
                $offset += 8 + $chunk['length'] + ($chunk['length'] % 2);
            }
        }
        else return false;

        $scale = min(1, $maxEdge / max($info[0], $info[1]));
        if ($source === $target && $scale === 1) return true;
        $image = imagecreatefromstring($bytes);
        if ($image === false) throw new RuntimeException('Cannot decode ' . $source);
        imagepalettetotruecolor($image);
        imagesavealpha($image, true);
        if ($scale < 1)
        {
            $resized = imagecreatetruecolor(max(1, (int)round($info[0] * $scale)), max(1, (int)round($info[1] * $scale)));
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $info[0], $info[1]);
            imagedestroy($image);
            $image = $resized;
        }
        $temporary = tempnam(dirname($target), '.image-');
        if ($temporary === false) throw new RuntimeException('Cannot create image staging file');
        try
        {
            if (!imagewebp($image, $temporary, IMG_WEBP_LOSSLESS)) throw new RuntimeException('Cannot encode image');
            $decoded = imagecreatefromwebp($temporary);
            if ($decoded === false || imagesx($decoded) !== imagesx($image) || imagesy($decoded) !== imagesy($image))
                throw new RuntimeException('Invalid generated WebP');
            imagedestroy($decoded);
            // A retry accepts only the exact output generated from this source.
            if ($source !== $target && is_file($target))
            {
                if (hash_file('sha256', $temporary) !== hash_file('sha256', $target))
                    throw new RuntimeException('Different image already exists: ' . $target);
                return true;
            }
            if (filesize($temporary) >= strlen($bytes)) return false;
            if (!rename($temporary, $target)) throw new RuntimeException('Cannot publish image: ' . $target);
            return true;
        }
        finally
        {
            imagedestroy($image);
            if (is_file($temporary)) unlink($temporary);
        }
    }
}
