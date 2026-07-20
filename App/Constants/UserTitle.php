<?php

declare(strict_types=1);

namespace App\Constants;

final class UserTitle
{
    public const EXPLORATEUR = 'Explorateur';
    public const AVENTURIER = 'Aventurier';
    public const VOYAGEUR = 'Voyageur';
    public const ECLAIREUR = 'Éclaireur';
    public const ERUDIT = 'Érudit';
    public const SAVANT = 'Savant';
    public const MAITRE = 'Maître';
    public const GRAND_MAITRE = 'Grand Maître';
    public const SAGE = 'Sage';
    public const ARCHISAGE = 'Archisage';
    public const CHAMPION = 'Champion';
    public const HEROS = 'Héros';
    public const GARDIEN = 'Gardien';
    public const SEIGNEUR = 'Seigneur';
    public const ARCHONTE = 'Archonte';
    public const LEGENDE = 'Légende';
    public const MYTHE = 'Mythe';
    public const IMMORTEL = 'Immortel';
    public const ETERNEL = 'Éternel';
    public const DIVIN = 'Divin';

    /**
     * @var array<int, string>
     */
    public const LEVEL_TITLES = [
        1   => self::EXPLORATEUR,
        5   => self::AVENTURIER,
        10  => self::VOYAGEUR,
        15  => self::ECLAIREUR,
        20  => self::ERUDIT,
        25  => self::SAVANT,
        30  => self::MAITRE,
        35  => self::GRAND_MAITRE,
        40  => self::SAGE,
        45  => self::ARCHISAGE,
        50  => self::CHAMPION,
        55  => self::HEROS,
        60  => self::GARDIEN,
        65  => self::SEIGNEUR,
        70  => self::ARCHONTE,
        75  => self::LEGENDE,
        80  => self::MYTHE,
        85  => self::IMMORTEL,
        90  => self::ETERNEL,
        100 => self::DIVIN,
    ];

    /**
     * @return list<string>
     */
    public static function unlockedTitles(int $level): array
    {
        $titles = [];

        foreach (self::LEVEL_TITLES as $requiredLevel => $title)
        {
            if ($level < $requiredLevel)
            {
                break;
            }

            $titles[] = $title;
        }

        return $titles;
    }
}