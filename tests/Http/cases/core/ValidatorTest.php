<?php

declare(strict_types=1);

namespace Tests\Http\Cases\Core;

use Framework\Validation\Validator;

final class ValidatorTest
{
    public static function run(): array
    {
        return [

            self::testRequired(),

            self::testInteger(),

            self::testMin(),

            self::testMax(),

            self::testMaxLength(),

            self::testAllowedValues(),

            self::testNullable(),

            self::testFails(),

        ];
    }

    private static function testRequired(): array
    {
        $validator = new Validator([]);

        $validator->required(
            'title',
        );

        return [
            'name' =>
                'Validator required',

            'success' =>
                $validator->fails(),
        ];
    }

    private static function testInteger(): array
    {
        $validator = new Validator([
            'note' => 10,
        ]);

        $validator->integer(
            'note',
        );

        return [
            'name' =>
                'Validator integer',

            'success' =>
                !$validator->fails(),
        ];
    }

    private static function testMin(): array
    {
        $validator = new Validator([
            'note' => 5,
        ]);

        $validator->min(
            'note',
            1,
        );

        return [
            'name' =>
                'Validator min',

            'success' =>
                !$validator->fails(),
        ];
    }

    private static function testMax(): array
    {
        $validator = new Validator([
            'note' => 25,
        ]);

        $validator->max(
            'note',
            20,
        );

        return [
            'name' =>
                'Validator max',

            'success' =>
                $validator->fails(),
        ];
    }

    private static function testMaxLength(): array
    {
        $validator = new Validator([
            'title' =>
                str_repeat(
                    'a',
                    300,
                ),
        ]);

        $validator->maxLength(
            'title',
            255,
        );

        return [
            'name' =>
                'Validator max length',

            'success' =>
                $validator->fails(),
        ];
    }

    private static function testAllowedValues(): array
    {
        $validator = new Validator([
            'status' => 'reading',
        ]);

        $validator->in(
            'status',
            [
                'reading',
                'finished',
            ],
        );

        return [
            'name' =>
                'Validator allowed values',

            'success' =>
                !$validator->fails(),
        ];
    }

    private static function testNullable(): array
    {
        $validator = new Validator([
            'description' => '',
        ]);

        $validator
            ->nullable(
                'description',
            )
            ->maxLength(
                'description',
                50,
            );

        return [
            'name' =>
                'Validator nullable',

            'success' =>
                !$validator->fails(),
        ];
    }

    private static function testFails(): array
    {
        $validator = new Validator([]);

        $validator->required(
            'title',
        );

        return [
            'name' =>
                'Validator fails',

            'success' =>
                $validator->fails(),
        ];
    }
}