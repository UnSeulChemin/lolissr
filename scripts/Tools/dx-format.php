<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli')
{
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/Support/SourceFormatter.php';

try
{
    $check = false;
    $config = __DIR__ . '/dx.json';
    foreach (array_slice($argv, 1) as $argument)
    {
        if ($argument === '--help')
        {
            echo "Usage: php scripts/Tools/dx-format.php [--check] [--config=chemin.json]\n";
            echo "Sans option : applique les conventions DX aux sources.\n";
            echo "--check : liste les fichiers a reformater sans les modifier (code 1 si ecarts).\n";
            exit(0);
        }
        if ($argument === '--check')
        {
            $check = true;
        }
        elseif (str_starts_with($argument, '--config='))
        {
            $config = substr($argument, strlen('--config='));
        }
        else
        {
            throw new InvalidArgumentException('Option inconnue : ' . $argument);
        }
    }

    $formatter = new SourceFormatter(dirname(__DIR__, 2), $config);
    exit($formatter->run($check));
}
catch (Throwable $error)
{
    fwrite(STDERR, 'DX : ' . $error->getMessage() . PHP_EOL);
    exit(2);
}
