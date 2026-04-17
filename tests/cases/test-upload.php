<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPLOAD
|--------------------------------------------------------------------------
*/

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

if ($testPostAjouter)
{
    addPostCheck($postChecks, [

        'category' => 'Upload',
        'label' => 'Upload image valide',

        'url' => rtrim($base, '/') . '/manga/ajouter',

        'callback' => static function () use ($base): array
        {
            $tmpFile = ROOT . '/tests/tmp-valid.jpg';
            $uploadDir = ROOT . '/tests/tmp-uploads';

            if (!is_dir($uploadDir))
            {
                mkdir($uploadDir, 0777, true);
            }

            createValidJpeg($tmpFile);

            $boundary = uniqid('boundary_', true);

            $body = buildMultipartBody(
                [
                    'livre' => 'Test Upload',
                    'slug' => 'test-upload',
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
                unlink($tmpFile);
            }

            $json = json_decode($response['body'], true);

            $okResponse =
                $response['status'] === 200
                && is_array($json)
                && isset($json['success'])
                && $json['success'] === true
                && isset($json['file'])
                && is_string($json['file'])
                && $json['file'] !== '';

            $fileExists = false;
            $returnedFile = '';

            if ($okResponse)
            {
                $returnedFile = $json['file'];
                $expectedPath = $uploadDir . '/' . $returnedFile;
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