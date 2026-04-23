<?php

declare(strict_types=1);

if (!function_exists('dd'))
{
    function dd(mixed ...$vars): void
    {
        echo '<pre style="background:#111;color:#0f0;padding:10px;">';

        foreach ($vars as $var)
        {
            var_dump($var);
        }

        echo '</pre>';

        die(1);
    }
}

if (!function_exists('dump'))
{
    function dump(mixed ...$vars): void
    {
        echo '<pre>';

        foreach ($vars as $var)
        {
            var_dump($var);
        }

        echo '</pre>';
    }
}