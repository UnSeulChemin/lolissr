<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\Manga\MangaSearchRepository;
use ReflectionClass;

final class MangaSearchRepositoryTest
{
    public static function run(): array
    {
        return [

            self::testNormalizeSearch(),

            self::testExtractNumero(),

            self::testExtractNumeroWithTome(),

            self::testInvalidNumero(),

        ];
    }

    private static function testNormalizeSearch(): array
    {
        $repository =
            new MangaSearchRepository();

        $reflection =
            new ReflectionClass(
                $repository,
            );

        $method =
            $reflection->getMethod(
                'normalizeSearch',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                $repository,
                '   one    piece   ',
            );

        return [
            'name' =>
                'MangaSearch normalize search',

            'success' =>
                $result === 'one piece',
        ];
    }

    private static function testExtractNumero(): array
    {
        $repository =
            new MangaSearchRepository();

        $reflection =
            new ReflectionClass(
                $repository,
            );

        $method =
            $reflection->getMethod(
                'extractSearchNumero',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                $repository,
                'one piece 12',
            );

        return [
            'name' =>
                'MangaSearch extract numero',

            'success' =>

                is_array($result)

                && $result['title']
                    === 'one piece'

                && $result['numero']
                    === 12,
        ];
    }

    private static function testExtractNumeroWithTome(): array
    {
        $repository =
            new MangaSearchRepository();

        $reflection =
            new ReflectionClass(
                $repository,
            );

        $method =
            $reflection->getMethod(
                'extractSearchNumero',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                $repository,
                'one piece tome 12',
            );

        return [
            'name' =>
                'MangaSearch extract tome numero',

            'success' =>

                is_array($result)

                && $result['numero']
                    === 12,
        ];
    }

    private static function testInvalidNumero(): array
    {
        $repository =
            new MangaSearchRepository();

        $reflection =
            new ReflectionClass(
                $repository,
            );

        $method =
            $reflection->getMethod(
                'extractSearchNumero',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                $repository,
                'one piece',
            );

        return [
            'name' =>
                'MangaSearch invalid numero',

            'success' =>
                $result === null,
        ];
    }
}