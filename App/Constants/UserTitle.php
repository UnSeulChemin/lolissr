<?php

declare(strict_types=1);

namespace App\Constants;

final class UserTitle
{
    public const FIGURINE_REWARD = 'Collection arc-en-ciel';
    public const FIGURINE_REWARD_TARGET = 4;
    public const ARTBOOK_REWARD = 'Gardien des illustrations';
    public const ARTBOOK_REWARD_TARGET = 10;
    public const TOME_REWARD = 'Voyageur des pages';
    public const TOME_REWARD_TARGET = 50;

    public static function styleForTitle(string $title): string
    {
        return match ($title) {
            self::FIGURINE_REWARD => 'rose-blue',
            self::ARTBOOK_REWARD => 'teal-gold',
            self::TOME_REWARD => 'violet-blue',
            default => '',
        };
    }

    // =========================================
    // TITRES
    // =========================================

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

    public const LECTEUR_CURIEUX = 'Lecteur curieux';
    public const CHASSEUR_DE_TOMES = 'Chasseur de tomes';
    public const COLLECTIONNEUR = 'Collectionneur';
    public const APPRENTI_SINOPHILE = 'Apprenti sinophile';
    public const GARDIEN_DE_BIBLIOTHEQUE = 'Gardien de bibliothèque';
    public const PASSIONNE_D_ASIE = 'Passionné d’Asie';
    public const BIBLIOPHILE = 'Bibliophile';
    public const CHASSEUR_DE_RARETES = 'Chasseur de raretés';
    public const CONNAISSEUR = 'Connaisseur';
    public const ARCHIVISTE = 'Archiviste';
    public const COLLECTIONNEUR_D_ELITE = 'Collectionneur d’élite';
    public const PASSEUR_DE_SAVOIR = 'Passeur de savoir';
    public const GARDIEN_DES_RECITS = 'Gardien des récits';
    public const CELESTE = 'Céleste';
    public const TRANSCENDANT = 'Transcendant';
    public const LEGENDE_SSR = 'Légende SSR';

    /**
     * @var array<int, string>
     */
    public const LEVEL_TITLES = [
        1 => self::EXPLORATEUR,
        3 => self::LECTEUR_CURIEUX,
        5 => self::AVENTURIER,
        8 => self::CHASSEUR_DE_TOMES,
        10 => self::VOYAGEUR,
        12 => self::COLLECTIONNEUR,
        15 => self::ECLAIREUR,
        18 => self::APPRENTI_SINOPHILE,
        20 => self::ERUDIT,
        22 => self::GARDIEN_DE_BIBLIOTHEQUE,
        25 => self::SAVANT,
        28 => self::PASSIONNE_D_ASIE,
        30 => self::MAITRE,
        32 => self::BIBLIOPHILE,
        35 => self::GRAND_MAITRE,
        38 => self::CHASSEUR_DE_RARETES,
        40 => self::SAGE,
        42 => self::CONNAISSEUR,
        45 => self::ARCHISAGE,
        48 => self::ARCHIVISTE,
        50 => self::CHAMPION,
        53 => self::COLLECTIONNEUR_D_ELITE,
        55 => self::HEROS,
        58 => self::PASSEUR_DE_SAVOIR,
        60 => self::GARDIEN,
        63 => self::GARDIEN_DES_RECITS,
        65 => self::SEIGNEUR,
        70 => self::ARCHONTE,
        75 => self::LEGENDE,
        80 => self::MYTHE,
        85 => self::IMMORTEL,
        90 => self::ETERNEL,
        95 => self::CELESTE,
        100 => self::DIVIN,
        125 => self::TRANSCENDANT,
        150 => self::LEGENDE_SSR,
    ];

    private function __construct()
    {
    }

    // =========================================
    // HELPERS
    // =========================================

    /** @return list<array{title: string, required_level: int, unlocked: bool, requirement: string, style: string}> */
    public static function titlesForLevel(int $level, int $figurinesCollected = 0, int $readArtbooks = 0, int $readTomes = 0): array
    {
        $titles = [];

        foreach (self::LEVEL_TITLES as $requiredLevel => $title)
        {
            $titles[] = [
                'title' => $title,
                'required_level' => $requiredLevel,
                'unlocked' => $level >= $requiredLevel,
                'requirement' => $requiredLevel > 1 ? 'Niveau ' . $requiredLevel : 'Disponible',
                'style' => '',
            ];
        }

        $titles[] = [
            'title' => self::FIGURINE_REWARD,
            'required_level' => 0,
            'unlocked' => $figurinesCollected >= self::FIGURINE_REWARD_TARGET,
            'requirement' => self::FIGURINE_REWARD_TARGET . ' figurines collectionnées',
            'style' => 'rose-blue',
        ];

        $titles[] = [
            'title' => self::ARTBOOK_REWARD,
            'required_level' => 0,
            'unlocked' => $readArtbooks >= self::ARTBOOK_REWARD_TARGET,
            'requirement' => self::ARTBOOK_REWARD_TARGET . ' artbooks lus',
            'style' => self::styleForTitle(self::ARTBOOK_REWARD),
        ];

        $titles[] = [
            'title' => self::TOME_REWARD,
            'required_level' => 0,
            'unlocked' => $readTomes >= self::TOME_REWARD_TARGET,
            'requirement' => self::TOME_REWARD_TARGET . ' tomes lus',
            'style' => self::styleForTitle(self::TOME_REWARD),
        ];

        usort($titles, static function (array $a, array $b): int
        {
            $groupA = $a['required_level'] === 0 ? ($a['unlocked'] ? 0 : 2) : 1;
            $groupB = $b['required_level'] === 0 ? ($b['unlocked'] ? 0 : 2) : 1;
            $comparison = $groupA <=> $groupB;

            return $comparison !== 0 ? $comparison : $a['required_level'] <=> $b['required_level'];
        });

        return $titles;
    }

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
