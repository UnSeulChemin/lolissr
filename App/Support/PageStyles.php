<?php

declare(strict_types=1);

namespace App\Support;

final class PageStyles
{
    /** @return list<string> */
    public static function forView(string $viewPath): array
    {
        $root = str_replace('\\', '/', view_path(''));
        $view = str_replace('\\', '/', $viewPath);
        $view = substr($view, strlen(rtrim($root, '/')) + 1);
        $view = preg_replace('/\.php$/', '', $view) ?? $view;

        /** @var array<string, list<string>> $dependencies */
        $dependencies = config('styles', []);
        $stylesheets = [];

        foreach ($dependencies as $file => $views)
        {
            foreach ($views as $pattern)
            {
                if ($view === $pattern || (str_ends_with($pattern, '/') && str_starts_with($view, $pattern)))
                {
                    $stylesheets[] = view_base_uri() . 'css/' . $file;
                    break;
                }
            }
        }

        return $stylesheets;
    }
}
