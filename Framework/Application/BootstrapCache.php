<?php

declare(strict_types=1);

namespace Framework\Application;

use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Config\EnvironmentValidator;
use Framework\Container\Container;
use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

use RuntimeException;
use Throwable;

// Deployment-local artifact. Rebuild after changing configuration or route sources.
final class BootstrapCache
{
    // Les espaces de noms des contrôleurs ont changé lors de la réorganisation du projet.
    private const VERSION = 4;

    public static function path(): string
    {
        return base_path('storage/bootstrap/compiled.php');
    }

    // Compiler après avoir chargé et validé l’environnement cible.
    public static function compile(): string
    {
        EnvironmentValidator::validate();
        Config::clear();
        $config = [];
        foreach (Config::names() as $name)
        {
            $value = Config::get($name);
            if (! is_array($value)) throw new RuntimeException('Configuration must return an array: ' . $name);
            $config[$name] = $value;
        }

        $routes = new RouteCollection();
        $register = require base_path('Config/routes/web.php');
        if (! is_callable($register)) throw new RuntimeException('Config/routes/web.php must return a callable.');
        $register(new Router($routes, new Container()));

        // Les actions anonymes ne sont pas sérialisables : conserver l’amorçage habituel
        // jusqu’à leur déplacement dans des contrôleurs. Les groupes anonymes sont déjà résolus.
        $serialized = serialize($routes);
        $keys = Env::accessedKeys();
        sort($keys);
        $payload = [
            'version' => self::VERSION,
            'root' => base_path(),
            'keys' => $keys,
            'environment' => self::fingerprint($keys),
            'validator' => self::validatorFingerprint(),
            'config' => $config,
            'routes' => $serialized
        ];

        return "<?php\n\ndeclare(strict_types=1);\n\n// Generated on the target host; contains private configuration.\nreturn "
            . var_export($payload, true) . ";\n";
    }

    /**
     * Retourne null sans charger la configuration si le fichier compilé est absent ou périmé.
     * @return array{config: array<string, array<string, mixed>>, routes: RouteCollection}|null
     */
    public static function load(string $path): ?array
    {
        if (! is_file($path)) return null;
        try
        {
            $payload = @require $path;
            if (! is_array($payload)
                || ($payload['version'] ?? null) !== self::VERSION
                || ($payload['root'] ?? null) !== base_path()
                || ($payload['validator'] ?? null) !== self::validatorFingerprint()
                || ! is_array($payload['keys'] ?? null)
                || ! is_string($payload['environment'] ?? null)
                || ! is_array($payload['config'] ?? null)
                || ! is_string($payload['routes'] ?? null)) return null;

            $keys = [];
            foreach ($payload['keys'] as $key)
            {
                if (! is_string($key)) return null;
                $keys[] = $key;
            }
            if (! hash_equals($payload['environment'], self::fingerprint($keys))) return null;

            $config = [];
            foreach ($payload['config'] as $name => $values)
            {
                if (! is_string($name) || ! is_array($values)) return null;
                foreach (array_keys($values) as $key)
                {
                    if (! is_string($key)) return null;
                }
                /** @var array<string, mixed> $values */
                $config[$name] = $values;
            }
            $routes = @unserialize($payload['routes'], ['allowed_classes' => [RouteCollection::class, Route::class]]);
            if (! $routes instanceof RouteCollection) return null;

            return ['config' => $config, 'routes' => $routes];
        }
        catch (Throwable)
        {
            // Un fichier de cache ne doit jamais empêcher un amorçage valide.
            return null;
        }
    }

    /** @param list<string> $keys */
    private static function fingerprint(array $keys): string
    {
        $values = [];
        foreach ($keys as $key)
        {
            $values[$key] = [Env::has($key), Env::get($key)];
        }
        return hash('sha256', serialize($values));
    }

    private static function validatorFingerprint(): string
    {
        $hash = hash_file('sha256', base_path('Framework/Config/EnvironmentValidator.php'));
        if ($hash === false) throw new RuntimeException('Cannot fingerprint environment validation.');
        return $hash;
    }
}
