<?php

declare(strict_types=1);

namespace Framework\Http;

use finfo;

final readonly class UploadedFile
{
    private finfo $finfo;

    public function __construct(
        private Request $request,
    ) {
        $this->finfo = new finfo(
            FILEINFO_MIME_TYPE,
        );
    }

    public function exists(
        string $key,
    ): bool {
        return $this->request->hasValidFile(
            $key,
        );
    }

    public function name(
        string $key,
    ): ?string {
        if (!$this->exists($key)) {
            return null;
        }

        $name = trim(
            $this->request->fileName($key),
        );

        return $name !== ''
            ? $name
            : null;
    }

    public function tmp(
        string $key,
    ): ?string {
        if (!$this->exists($key)) {
            return null;
        }

        $tmp = trim(
            $this->request->fileTmpPath($key),
        );

        return $tmp !== ''
            ? $tmp
            : null;
    }

    public function error(
        string $key,
    ): ?int {
        if (!$this->request->hasFile($key)) {
            return null;
        }

        return $this->request->fileError($key);
    }

    public function size(
        string $key,
    ): ?int {
        if (!$this->exists($key)) {
            return null;
        }

        return $this->request->fileSize($key);
    }

    public function extension(
        string $key,
    ): ?string {
        $name = $this->name($key);

        if ($name === null) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $name,
                PATHINFO_EXTENSION,
            ),
        );

        if ($extension === '') {
            return null;
        }

        return $extension === 'jpeg'
            ? 'jpg'
            : $extension;
    }

    public function mimeType(
        string $key,
    ): ?string {
        $tmpName = $this->tmp($key);

        if (
            $tmpName === null
            || !is_file($tmpName)
        ) {
            return null;
        }

        $mimeType = $this->finfo->file(
            $tmpName,
        );

        return is_string($mimeType)
            && $mimeType !== ''
                ? strtolower($mimeType)
                : null;
    }
}