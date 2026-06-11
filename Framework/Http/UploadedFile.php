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
        return isset(
            $this->request->files()[$key],
        )
        && (
            $this->request->files()[$key]['error']
            ?? UPLOAD_ERR_NO_FILE
        ) === UPLOAD_ERR_OK;
    }

    public function name(
        string $key,
    ): ?string {

        if (! $this->exists($key))
        {
            return null;
        }

        $name =
            trim(
                (string) (
                    $this->request->files()[$key]['name']
                    ?? ''
                ),
            );

        return $name !== ''
            ? $name
            : null;
    }

    public function tmp(
        string $key,
    ): ?string {

        if (! $this->exists($key))
        {
            return null;
        }

        $tmp =
            trim(
                (string) (
                    $this->request->files()[$key]['tmp_name']
                    ?? ''
                ),
            );

        return $tmp !== ''
            ? $tmp
            : null;
    }

    public function error(
        string $key,
    ): ?int {

        if (
            ! isset(
                $this->request->files()[$key],
            )
        ) {
            return null;
        }

        return (int) (
            $this->request->files()[$key]['error']
            ?? UPLOAD_ERR_NO_FILE
        );
    }

    public function size(
        string $key,
    ): ?int {

        if (! $this->exists($key))
        {
            return null;
        }

        return (int) (
            $this->request->files()[$key]['size']
            ?? 0
        );
    }

    public function extension(
        string $key,
    ): ?string {

        $name =
            $this->name(
                $key,
            );

        if ($name === null)
        {
            return null;
        }

        $extension =
            strtolower(
                pathinfo(
                    $name,
                    PATHINFO_EXTENSION,
                ),
            );

        if ($extension === '')
        {
            return null;
        }

        return $extension === 'jpeg'
            ? 'jpg'
            : $extension;
    }

    public function mimeType(
        string $key,
    ): ?string {

        $tmp =
            $this->tmp(
                $key,
            );

        if (
            $tmp === null
            || ! is_file($tmp)
        ) {
            return null;
        }

        $mimeType =
            $this->finfo->file(
                $tmp,
            );

        return is_string(
            $mimeType,
        )
        && $mimeType !== ''
            ? strtolower(
                $mimeType,
            )
            : null;
    }
}