<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{ http_response_code(404); exit; }
require_once __DIR__ . '/Support/MigrationCreator.php';
try
{
    if (count($argv) !== 2) throw new InvalidArgumentException('Usage: composer db:migration -- ajout-table');
    $path = MigrationCreator::create(__DIR__ . '/migrations', $argv[1]);
    echo 'Migration creee : scripts/Database/migrations/' . basename($path) . PHP_EOL;
    echo "Ecris ton SQL dans ce fichier, puis sauvegarde-le. Aucun SQL execute pour le moment.\n";
    echo "Ensuite : composer db:migrate (base configuree dans .env). Le fichier vide est refuse.\n";
    echo "Une fois appliquee, conserve la migration telle quelle ; cree un nouveau fichier pour la suite.\n";
}
catch (Throwable $error)
{
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
