<?php

declare(strict_types=1);

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredPassesWhenFieldIsFilled(): void
    {
        $validator = new Validator([
            'livre' => 'One Piece'
        ], []);

        $validator->required('livre', 'Le titre est obligatoire.');

        $this->assertSame([], $validator->errors());
    }

    public function testRequiredFailsWhenFieldIsMissing(): void
    {
        $validator = new Validator([], []);

        $validator->required('livre', 'Le titre est obligatoire.');

        $this->assertArrayHasKey('livre', $validator->errors());
        $this->assertSame('Le titre est obligatoire.', $validator->errors()['livre']);
    }

    public function testStringPassesWithStringValue(): void
    {
        $validator = new Validator([
            'slug' => 'one-piece'
        ], []);

        $validator->string('slug', 'Le slug doit être une chaîne.');

        $this->assertSame([], $validator->errors());
    }

    public function testStringFailsWithNonStringValue(): void
    {
        $validator = new Validator([
            'slug' => ['one-piece']
        ], []);

        $validator->string('slug', 'Le slug doit être une chaîne.');

        $this->assertArrayHasKey('slug', $validator->errors());
        $this->assertSame('Le slug doit être une chaîne.', $validator->errors()['slug']);
    }

    public function testIntegerPassesWithNumericString(): void
    {
        $validator = new Validator([
            'numero' => '12'
        ], []);

        $validator->integer('numero', 'Le numéro doit être un entier.');

        $this->assertSame([], $validator->errors());
    }

    public function testIntegerFailsWithInvalidValue(): void
    {
        $validator = new Validator([
            'numero' => 'abc'
        ], []);

        $validator->integer('numero', 'Le numéro doit être un entier.');

        $this->assertArrayHasKey('numero', $validator->errors());
        $this->assertSame('Le numéro doit être un entier.', $validator->errors()['numero']);
    }

    public function testMinPassesWhenValueIsGreaterThanOrEqualToMinimum(): void
    {
        $validator = new Validator([
            'numero' => '1'
        ], []);

        $validator->min('numero', 1, 'Le numéro doit être supérieur ou égal à 1.');

        $this->assertSame([], $validator->errors());
    }

    public function testMinFailsWhenValueIsSmallerThanMinimum(): void
    {
        $validator = new Validator([
            'numero' => '0'
        ], []);

        $validator->min('numero', 1, 'Le numéro doit être supérieur ou égal à 1.');

        $this->assertArrayHasKey('numero', $validator->errors());
        $this->assertSame('Le numéro doit être supérieur ou égal à 1.', $validator->errors()['numero']);
    }

    public function testMaxPassesWhenValueIsLowerThanOrEqualToMaximum(): void
    {
        $validator = new Validator([
            'note' => '5'
        ], []);

        $validator->max('note', 5, 'La note doit être inférieure ou égale à 5.');

        $this->assertSame([], $validator->errors());
    }

    public function testMaxFailsWhenValueIsGreaterThanMaximum(): void
    {
        $validator = new Validator([
            'note' => '6'
        ], []);

        $validator->max('note', 5, 'La note doit être inférieure ou égale à 5.');

        $this->assertArrayHasKey('note', $validator->errors());
        $this->assertSame('La note doit être inférieure ou égale à 5.', $validator->errors()['note']);
    }

    public function testMaxLengthPassesWhenLengthIsValid(): void
    {
        $validator = new Validator([
            'commentaire' => 'Très bon tome'
        ], []);

        $validator->maxLength('commentaire', 1000, 'Le commentaire est trop long.');

        $this->assertSame([], $validator->errors());
    }

    public function testMaxLengthFailsWhenLengthIsTooLong(): void
    {
        $validator = new Validator([
            'commentaire' => str_repeat('a', 1001)
        ], []);

        $validator->maxLength('commentaire', 1000, 'Le commentaire est trop long.');

        $this->assertArrayHasKey('commentaire', $validator->errors());
        $this->assertSame('Le commentaire est trop long.', $validator->errors()['commentaire']);
    }

    public function testInPassesWhenValueIsAllowed(): void
    {
        $validator = new Validator([
            'extension' => 'jpg'
        ], []);

        $validator->in('extension', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $this->assertSame([], $validator->errors());
    }

    public function testInFailsWhenValueIsNotAllowed(): void
    {
        $validator = new Validator([
            'extension' => 'gif'
        ], []);

        $validator->in('extension', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $this->assertArrayHasKey('extension', $validator->errors());
        $this->assertSame('Extension invalide.', $validator->errors()['extension']);
    }

    public function testNullableFieldDoesNotTriggerOtherRulesWhenEmpty(): void
    {
        $validator = new Validator([
            'commentaire' => ''
        ], []);

        $validator
            ->nullable('commentaire')
            ->maxLength('commentaire', 1000, 'Le commentaire est trop long.');

        $this->assertSame([], $validator->errors());
    }

    public function testNullableCanBeCalledTwiceWithoutIssue(): void
    {
        $validator = new Validator([
            'commentaire' => ''
        ], []);

        $validator
            ->nullable('commentaire')
            ->nullable('commentaire')
            ->maxLength('commentaire', 5, 'Le commentaire est trop long.');

        $this->assertSame([], $validator->errors());
    }

    public function testFileRequiredFailsWhenNoFileWasUploaded(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_NO_FILE
            ]
        ]);

        $validator->fileRequired('image', 'L’image est obligatoire.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('L’image est obligatoire.', $validator->errors()['image']);
    }

    public function testFileRequiredPassesWhenFileWasUploaded(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK
            ]
        ]);

        $validator->fileRequired('image', 'L’image est obligatoire.');

        $this->assertSame([], $validator->errors());
    }

    public function testFileOkFailsWhenUploadHasError(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_CANT_WRITE
            ]
        ]);

        $validator->fileOk('image', 'Erreur upload.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('Erreur upload.', $validator->errors()['image']);
    }

    public function testFileOkPassesWhenUploadIsSuccessful(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK
            ]
        ]);

        $validator->fileOk('image', 'Erreur upload.');

        $this->assertSame([], $validator->errors());
    }

    public function testImageExtensionPassesWithAllowedExtension(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'name' => 'cover.png'
            ]
        ]);

        $validator->imageExtension('image', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $this->assertSame([], $validator->errors());
    }

    public function testImageExtensionNormalizesJpegToJpg(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'name' => 'cover.jpeg'
            ]
        ]);

        $validator->imageExtension('image', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $this->assertSame([], $validator->errors());
    }

    public function testImageExtensionFailsWithDisallowedExtension(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'name' => 'cover.gif'
            ]
        ]);

        $validator->imageExtension('image', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('Extension invalide.', $validator->errors()['image']);
    }

    public function testImageExtensionFailsWhenFilenameIsMissing(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'name' => ''
            ]
        ]);

        $validator->imageExtension('image', ['jpg', 'png'], 'Extension invalide.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('Extension invalide.', $validator->errors()['image']);
    }

    public function testImageMimePassesWithAllowedMimeType(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'img');
        $this->assertNotFalse($tmpFile);

        file_put_contents(
            $tmpFile,
            base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+yF9kAAAAASUVORK5CYII='
            )
        );

        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => $tmpFile
            ]
        ]);

        $validator->imageMime('image', ['image/png'], 'MIME invalide.');

        $this->assertSame([], $validator->errors());

        @unlink($tmpFile);
    }

    public function testImageMimeFailsWhenTemporaryFileIsMissing(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => ''
            ]
        ]);

        $validator->imageMime('image', ['image/png'], 'MIME invalide.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('MIME invalide.', $validator->errors()['image']);
    }

    public function testImageMimeFailsWithDisallowedMimeType(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'txt');
        $this->assertNotFalse($tmpFile);

        file_put_contents($tmpFile, 'not an image');

        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => $tmpFile
            ]
        ]);

        $validator->imageMime('image', ['image/png'], 'MIME invalide.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('MIME invalide.', $validator->errors()['image']);

        @unlink($tmpFile);
    }

    public function testMaxFileSizePassesWhenSizeIsWithinLimit(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ]);

        $validator->maxFileSize('image', 2048, 'Fichier trop lourd.');

        $this->assertSame([], $validator->errors());
    }

    public function testMaxFileSizeAcceptsNumericString(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'size' => '1024'
            ]
        ]);

        $validator->maxFileSize('image', 2048, 'Fichier trop lourd.');

        $this->assertSame([], $validator->errors());
    }

    public function testMaxFileSizeFailsWhenSizeIsInvalid(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'size' => 'abc'
            ]
        ]);

        $validator->maxFileSize('image', 2048, 'Taille invalide.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('Taille invalide.', $validator->errors()['image']);
    }

    public function testMaxFileSizeFailsWhenFileIsTooLarge(): void
    {
        $validator = new Validator([], [
            'image' => [
                'error' => UPLOAD_ERR_OK,
                'size' => 4096
            ]
        ]);

        $validator->maxFileSize('image', 2048, 'Fichier trop lourd.');

        $this->assertArrayHasKey('image', $validator->errors());
        $this->assertSame('Fichier trop lourd.', $validator->errors()['image']);
    }

    public function testFailsReturnsFalseWhenThereAreNoErrors(): void
    {
        $validator = new Validator([
            'livre' => 'One Piece'
        ], []);

        $validator->required('livre', 'Le titre est obligatoire.');

        $this->assertFalse($validator->fails());
    }

    public function testFailsReturnsTrueWhenThereIsAtLeastOneError(): void
    {
        $validator = new Validator([], []);

        $validator->required('livre', 'Le titre est obligatoire.');

        $this->assertTrue($validator->fails());
    }

    public function testExistingErrorPreventsFollowingValidationForSameField(): void
    {
        $validator = new Validator([
            'numero' => 'abc'
        ], []);

        $validator
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur ou égal à 1.')
            ->max('numero', 5, 'Le numéro doit être inférieur ou égal à 5.');

        $errors = $validator->errors();

        $this->assertCount(1, $errors);
        $this->assertSame('Le numéro doit être un entier.', $errors['numero']);
    }

    public function testChainedRulesCollectErrorsProperly(): void
    {
        $validator = new Validator([
            'livre' => '',
            'slug' => ['bad'],
            'numero' => '0',
            'commentaire' => str_repeat('x', 1001),
            'extension' => 'gif'
        ], []);

        $validator
            ->required('livre', 'Le titre est obligatoire.')
            ->string('slug', 'Le slug doit être une chaîne.')
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur ou égal à 1.')
            ->maxLength('commentaire', 1000, 'Le commentaire est trop long.')
            ->in('extension', ['jpg', 'png', 'webp'], 'Extension invalide.');

        $errors = $validator->errors();

        $this->assertArrayHasKey('livre', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('numero', $errors);
        $this->assertArrayHasKey('commentaire', $errors);
        $this->assertArrayHasKey('extension', $errors);
    }
}