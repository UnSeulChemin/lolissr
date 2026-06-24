<?php

declare(strict_types=1);

namespace App\Constants;

final class UserTitle
{
    public const EXPLORATEUR = 'Explorateur';
    public const COLLECTIONNEUR = 'Collectionneur';
    public const BIBLIOTHECAIRE = 'Bibliothécaire';
    public const ERUDIT = 'Érudit';
    public const MAITRE = 'Maître';
    public const SAGE = 'Sage';
    public const LEGENDE = 'Légende';

    public const LEVEL_TITLES = [
        1 => self::EXPLORATEUR,
        5 => self::COLLECTIONNEUR,
        10 => self::BIBLIOTHECAIRE,
        15 => self::ERUDIT,
        20 => self::MAITRE,
        25 => self::SAGE,
        30 => self::LEGENDE,
    ];

    /**
     * @return list<string>
     */
    public static function unlockedTitles(int $level): array
    {
        $titles = [];

        foreach (self::LEVEL_TITLES as $requiredLevel => $title)
        {
            if ($level >= $requiredLevel)
            {
                $titles[] = $title;
            }
        }

        return $titles;
    }
}