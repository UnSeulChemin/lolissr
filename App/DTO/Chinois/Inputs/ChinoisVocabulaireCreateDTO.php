<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Inputs;

use Framework\Support\Str;

final readonly class ChinoisVocabulaireCreateDTO
{
    public function __construct(
        public string $langue,
        public string $mot,
        public string $pinyin,
        public string $type,
        public string $traduction,
        public ?string $exemple,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(
        array $data,
    ): self {

        return new self(
            langue: trim(
                (string) ($data['langue'] ?? ''),
            ),

            mot: trim(
                (string) ($data['mot'] ?? ''),
            ),

            pinyin: trim(
                (string) ($data['pinyin'] ?? ''),
            ),

            type: trim(
                (string) ($data['type'] ?? ''),
            ),

            traduction: trim(
                (string) ($data['traduction'] ?? ''),
            ),

            exemple: Str::nullableTrim(
                $data['exemple'] ?? null,
            ),
        );
    }

    /**
     * @param array<string, mixed> $post
     */
    public static function fromPost(
        array $post,
    ): self {
        return self::fromArray(
            $post,
        );
    }
}