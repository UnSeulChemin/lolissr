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

/** Deployment-local artifact. Rebuild after changing configuration or route sources. */
final class BootstrapCache
{
    private const VERSION = 2;

    public static function path(): string
    {
        return base_path('storage/bootstrap/compiled.php');
    }

    /** Build only after loading and validating the target environment. */
    public static function compile(): string
    {
        EnvironmentValidator::validate();
        Config::clear();
        $config = [];
        $files = glob(base_path('Config/*.php'));
        if ($files === false) throw new RuntimeException('Cannot list configuration files.');
        foreach ($files as $file)
        {
            $name = basename($file, '.php');
            if ($name === 'routes') continue;
            $value = Config::get($name);
            if (! is_array($value)) throw new RuntimeException('Configuration must return an array: ' . $name);
            $config[$name] = $value;
        }

        $routes = new RouteCollection();
        $register = require base_path('Config/routes.php');
        if (! is_callable($register)) throw new RuntimeException('Config/routes.php must return a callable.');
        $register(new Router($routes, new Container()));

        // Closure actions intentionally fail serialization: keep using normal bootstrap
        // until those actions are moved to controllers. Group closures are already resolved.
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
            'routes' => $serialized,
        ];

        return "<?php\n\ndeclare(strict_types=1);\n\n// Generated on the target host; contains private configuration.\nreturn "
            . var_export($payload, true) . ";\n";
    }

    /**
     * Returns null without priming configuration when the artifact is absent or stale.
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
            // A cache artifact must never make an otherwise valid bootstrap unavailable.
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
