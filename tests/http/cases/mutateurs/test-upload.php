<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPLOAD
|--------------------------------------------------------------------------
*/

if (!function_exists('createValidJpeg'))
{
    function createValidJpeg(string $path, int $width = 10, int $height = 10): void
    {
        $image = imagecreatetruecolor($width, $height);

        if ($image === false)
        {
            throw new RuntimeException('Impossible de créer une image JPEG de test.');
        }

        $background = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $background);

        imagejpeg($image, $path, 90);
        imagedestroy($image);
    }
}

if (!function_exists('createInvalidTextFile'))
{
    function createInvalidTextFile(string $path): void
    {
        $written = file_put_contents($path, "ceci n'est pas une image");

        if ($written === false)
        {
            throw new RuntimeException('Impossible de créer un faux fichier de test.');
        }
    }
}

if (!function_exists('decodeJsonResponse'))
{
    function decodeJsonResponse(string $body): ?array
    {
        $json = json_decode($body, true);

        return is_array($json) ? $json : null;
    }
}

if (!function_exists('buildTmpTestFile'))
{
    function buildTmpTestFile(string $directory, string $filename): string
    {
        ensureDirectory($directory);

        return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;
    }
}

if ($testPostAjouter)
{
    addPostCheck($postChecks, [

        'category' => 'Upload',
        'label' => 'Upload image valide (mode test)',

        'url' => rtrim($base, '/') . '/manga/ajouter',

        'callback' => static function () use ($base, $tmpUploadsDirectory): array
        {
            $tmpFile = buildTmpTestFile($tmpUploadsDirectory, 'tmp-valid.jpg');
            $unique = uniqid('', true);

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            createValidJpeg($tmpFile);

            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'livre' => 'Test Upload ' . $unique,
                    'slug' => 'test-upload-' . md5($unique),
                    'numero' => '999',
                ],
                [
                    'image' => [
                        'filename' => 'valid.jpg',
                        'path' => $tmpFile,
                        'type' => 'image/jpeg',
                    ]
                ],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/ajouter',
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                ],
                $body
            );

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            $json = decodeJsonResponse($response['body']);

            $okResponse =
                $response['status'] === 200
                && is_array($json)
                && ($json['success'] ?? null) === true
                && isset($json['file'])
                && is_string($json['file'])
                && $json['file'] !== '';

            $fileExists = false;
            $returnedFile = '';

            if ($okResponse)
            {
                $returnedFile = $json['file'];
                $expectedPath = rtrim($tmpUploadsDirectory, '/\\') . DIRECTORY_SEPARATOR . $returnedFile;
                $fileExists = is_file($expectedPath);
            }

            return [
                'ok' => $okResponse && $fileExists,
                'message' => 'status ' . $response['status']
                    . ' | fichier: '
                    . ($fileExists ? 'présent' : 'absent')
                    . ($returnedFile !== '' ? ' (' . $returnedFile . ')' : ''),
            ];
        }

    ]);
}

if ($testUploadDuplicateSlugNumero)
{
    addPostCheck($postChecks, [

        'category' => 'Upload',
        'label' => 'Upload doublon slug + numero (mode test)',

        'url' => rtrim($base, '/') . '/manga/ajouter',

        'callback' => static function () use ($base, $tmpUploadsDirectory): array
        {
            $tmpFile = buildTmpTestFile($tmpUploadsDirectory, 'tmp-duplicate.jpg');
            $unique = uniqid('', true);
            $livre = 'Test Duplicate ' . $unique;
            $slug = 'test-duplicate-slug-' . md5($unique);
            $numero = '777';

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            createValidJpeg($tmpFile);

            $boundary1 = uniqid('boundary_', true);

            $body1 = buildMultipartBody(
                [
                    'livre' => $livre,
                    'slug' => $slug,
                    'numero' => $numero,
                ],
                [
                    'image' => [
                        'filename' => 'valid.jpg',
                        'path' => $tmpFile,
                        'type' => 'image/jpeg',
                    ]
                ],
                $boundary1
            );

            $firstResponse = requestUrl(
                rtrim($base, '/') . '/manga/ajouter',
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary1",
                    'X-Requested-With: XMLHttpRequest',
                ],
                $body1
            );

            $firstJson = decodeJsonResponse($firstResponse['body']);

            $firstOk =
                $firstResponse['status'] === 200
                && is_array($firstJson)
                && ($firstJson['success'] ?? null) === true;

            if (!$firstOk)
            {
                if (is_file($tmpFile))
                {
                    @unlink($tmpFile);
                }

                return [
                    'ok' => false,
                    'message' => '1er upload test impossible',
                ];
            }

            createValidJpeg($tmpFile);

            $boundary2 = uniqid('boundary_', true);

            $body2 = buildMultipartBody(
                [
                    'livre' => $livre,
                    'slug' => $slug,
                    'numero' => $numero,
                ],
                [
                    'image' => [
                        'filename' => 'valid.jpg',
                        'path' => $tmpFile,
                        'type' => 'image/jpeg',
                    ]
                ],
                $boundary2
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/ajouter',
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary2",
                    'X-Requested-With: XMLHttpRequest',
                ],
                $body2
            );

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            $json = decodeJsonResponse($response['body']);

            $hasError =
                is_array($json)
                && ($json['success'] ?? null) === false;

            return [
                'ok' => $hasError || $response['status'] >= 400,
                'message' => 'status ' . $response['status'] . ' | doublon refusé',
            ];
        }

    ]);
}

if ($testUploadInvalidImage)
{
    addPostCheck($postChecks, [

        'category' => 'Upload',
        'label' => 'Refuse image invalide',

        'url' => rtrim($base, '/') . '/manga/ajouter',

        'callback' => static function () use ($base, $tmpUploadsDirectory): array
        {
            $tmpFile = buildTmpTestFile($tmpUploadsDirectory, 'tmp-invalid.txt');
            $unique = uniqid('', true);

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            createInvalidTextFile($tmpFile);

            $slug = 'test-invalid-image-' . md5($unique);
            $numero = '778';

            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'livre' => 'Test Invalid Image ' . $unique,
                    'slug' => $slug,
                    'numero' => $numero,
                ],
                [
                    'image' => [
                        'filename' => 'fake.txt',
                        'path' => $tmpFile,
                        'type' => 'text/plain',
                    ]
                ],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/ajouter',
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                ],
                $body
            );

            if (is_file($tmpFile))
            {
                @unlink($tmpFile);
            }

            $json = decodeJsonResponse($response['body']);

            $hasError =
                is_array($json)
                && ($json['success'] ?? null) === false;

            return [
                'ok' => $hasError || $response['status'] >= 400,
                'message' => 'status ' . $response['status'] . ' | image invalide refusée',
            ];
        }

    ]);
}

if ($testUploadMaxSize)
{
    addPostCheck($postChecks, [

        'category' => 'Upload',
        'label' => 'Refuse fichier trop volumineux',

        'url' => rtrim($base, '/') . '/manga/ajouter',

        'callback' => static function () use ($base, $fixturesDirectory): array
        {
            $fixtureFile = rtrim($fixturesDirectory, '/\\') . DIRECTORY_SEPARATOR . 'large.jpg';
            $unique = uniqid('', true);

            if (!is_file($fixtureFile))
            {
                return [
                    'ok' => false,
                    'message' => 'fixture large.jpg introuvable',
                ];
            }

            $slug = 'test-large-upload-' . md5($unique);
            $numero = '779';

            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'livre' => 'Test Large Upload ' . $unique,
                    'slug' => $slug,
                    'numero' => $numero,
                ],
                [
                    'image' => [
                        'filename' => 'large.jpg',
                        'path' => $fixtureFile,
                        'type' => 'image/jpeg',
                    ]
                ],
                $boundary
            );

            $response = requestUrl(
                rtrim($base, '/') . '/manga/ajouter',
                'POST',
                [
                    "Content-Type: multipart/form-data; boundary=$boundary",
                    'X-Requested-With: XMLHttpRequest',
                ],
                $body
            );

            $json = decodeJsonResponse($response['body']);

            $hasJsonError =
                is_array($json)
                && ($json['success'] ?? null) === false;

            $message = '';

            if (is_array($json) && isset($json['message']) && is_string($json['message']))
            {
                $message = $json['message'];
            }
            else
            {
                $message = $response['body'];
            }

            $hasSizeMessage =
                stripos($message, 'trop volumineux') !== false
                || stripos($message, 'taille maximale') !== false
                || stripos($message, 'trop lourd') !== false
                || stripos($message, 'taille') !== false;

            return [
                'ok' => ($response['status'] >= 400) || $hasJsonError || $hasSizeMessage,
                'message' => 'status ' . $response['status']
                    . ' | taille max: '
                    . (($hasJsonError || $hasSizeMessage) ? 'refusée' : 'non détectée'),
            ];
        }

    ]);
}