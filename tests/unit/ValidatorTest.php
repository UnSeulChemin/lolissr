<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

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
        $this->assertContains('Le titre est obligatoire.', $validator->errors()['livre']);
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
        $this->assertContains('Le slug doit être une chaîne.', $validator->errors()['slug']);
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
        $this->assertContains('Le commentaire est trop long.', $validator->errors()['commentaire']);
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
        $this->assertContains('Le numéro doit être un entier.', $validator->errors()['numero']);
    }

    public function testMinPassesWhenValueIsGreaterThanMinimum(): void
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
        $this->assertContains('Le numéro doit être supérieur ou égal à 1.', $validator->errors()['numero']);
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

    public function testChainedRulesCollectErrorsProperly(): void
    {
        $validator = new Validator([
            'livre' => '',
            'slug' => ['bad'],
            'numero' => '0',
            'commentaire' => str_repeat('x', 1001),
        ], []);

        $validator
            ->required('livre', 'Le titre est obligatoire.')
            ->string('slug', 'Le slug doit être une chaîne.')
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur ou égal à 1.')
            ->maxLength('commentaire', 1000, 'Le commentaire est trop long.');

        $errors = $validator->errors();

        $this->assertArrayHasKey('livre', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('numero', $errors);
        $this->assertArrayHasKey('commentaire', $errors);
    }
}