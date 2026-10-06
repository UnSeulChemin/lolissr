<?php

declare(strict_types=1);

final class MigrationCreator
{
    public static function create(string $directory, string $name): string
    {
        if (strlen($name) > 80 || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $name) !== 1)
            throw new InvalidArgumentException('Nom attendu : ajout-table, lettres minuscules/chiffres separes par des tirets (80 caracteres maximum).');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory))
            throw new RuntimeException('Impossible de creer le dossier des migrations.');
        $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d-His-u');
        $path = $directory . '/' . $timestamp . '-' . $name . '.sql';
        // Exclusive creation preserves any existing migration. Empty SQL cannot be applied.
        $handle = @fopen($path, 'xb');
        if ($handle === false) throw new RuntimeException('Impossible de creer la migration sans remplacer un fichier existant.');
        fclose($handle);
        return $path;
    }
}
