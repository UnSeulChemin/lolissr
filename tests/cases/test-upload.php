<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| TESTS UPLOAD RÉELS
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

/*
|--------------------------------------------------------------------------
| Upload valide
|--------------------------------------------------------------------------
*/

addPostCheck($postChecks, [
    'category' => 'Upload',
    'label' => 'Upload image valide',
    'url' => $base . '/manga/ajouter',
    'callback' => static function () use ($base): array
    {
        $tmpFile = __DIR__ . '/../tmp-valid.jpg';

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
                    'filename' => 'test.jpg',
                    'path' => $tmpFile,
                    'type' => 'image/jpeg',
                ]
            ],
            $boundary
        );

        $response = requestUrl(
            $base . '/manga/ajouter',
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

        $ok = $response['status'] === 200
            && is_array($json)
            && isset($json['success'])
            && $json['success'] === true;

        return [
            'ok' => $ok,
            'message' => 'status ' . $response['status'],
        ];
    }
]);