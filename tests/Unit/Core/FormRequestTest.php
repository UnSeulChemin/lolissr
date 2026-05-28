<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\FormRequest;
use Framework\Http\Request;

final class FormRequestTest
{
    public static function run(): array
    {
        return [

            self::testPasses(),

            self::testFails(),

            self::testValidated(),

            self::testData(),

            self::testFiles(),

            self::testAll(),

        ];
    }

    private static function testPasses(): array
    {
        $request = new Request(
            post: [
                'title' => 'Rave',
            ],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest passes',

            'success' =>
                $formRequest->passes(),
        ];
    }

    private static function testFails(): array
    {
        $request = new Request(
            post: [],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest fails',

            'success' =>
                $formRequest->fails(),
        ];
    }

    private static function testValidated(): array
    {
        $request = new Request(
            post: [
                'title' => 'Rave',
            ],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest validated',

            'success' =>
                $formRequest->validated()['title']
                    === 'Rave',
        ];
    }

    private static function testData(): array
    {
        $request = new Request(
            post: [
                'title' => 'Rave',
            ],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest data',

            'success' =>
                $formRequest->data()['title']
                    === 'Rave',
        ];
    }

    private static function testFiles(): array
    {
        $request = new Request(
            files: [
                'image' => [
                    'name' =>
                        'cover.jpg',
                ],
            ],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest files',

            'success' =>
                isset(
                    $formRequest->files()['image'],
                ),
        ];
    }

    private static function testAll(): array
    {
        $request = new Request(
            post: [
                'title' => 'Rave',
            ],
        );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        return [
            'name' =>
                'FormRequest all',

            'success' =>
                $formRequest->all()['title']
                    === 'Rave',
        ];
    }
}

final class FakeFormRequest
    extends FormRequest
{
    protected function validate(): void
    {
        $this->validator
            ->required(
                'title',
            );
    }

    public function dto(): object
    {
        return (object) [
            'title' =>
                $this->input(
                    'title',
                ),
        ];
    }
}