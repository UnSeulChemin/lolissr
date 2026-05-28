<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\FormRequest;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class FormRequestTest extends TestCase
{
    public function testPasses(): void
    {
        $request =
            new Request(
                post: [
                    'title' => 'Rave',
                ],
            );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        $this->assertTrue(
            $formRequest->passes(),
        );
    }

    public function testFails(): void
    {
        $request =
            new Request(
                post: [],
            );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        $this->assertTrue(
            $formRequest->fails(),
        );
    }

    public function testValidated(): void
    {
        $request =
            new Request(
                post: [
                    'title' => 'Rave',
                ],
            );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        $this->assertSame(
            'Rave',
            $formRequest->validated()['title'],
        );
    }

    public function testFiles(): void
    {
        $request =
            new Request(
                files: [
                    'image' => [
                        'name' => 'cover.jpg',
                    ],
                ],
            );

        $formRequest =
            new FakeFormRequest(
                $request,
            );

        $this->assertArrayHasKey(
            'image',
            $formRequest->files(),
        );
    }
}

final class FakeFormRequest extends FormRequest
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