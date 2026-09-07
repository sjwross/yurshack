<?php
declare(strict_types=1);

/**
 * Load environment variables from .env (if present) and expose helpers.
 */
final class AppConfig
{
    private static bool $loaded = false;

    /** @var array<string, string> */
    private static array $env = [];

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $candidates = [
            dirname(__DIR__) . '/.env',
            dirname(__DIR__, 2) . '/.env',
            dirname(__DIR__, 3) . '/.env.yurshack',
        ];

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                self::parseEnvFile($path);
                break;
            }
        }
    }

    private static function parseEnvFile(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            self::$env[$key] = $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();
        $fromEnv = getenv($key);
        if ($fromEnv !== false && $fromEnv !== '') {
            return $fromEnv;
        }
        if (array_key_exists($key, self::$env) && self::$env[$key] !== '') {
            return self::$env[$key];
        }
        return $default;
    }

    public static function requireValue(string $key): string
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            throw new RuntimeException('Missing required configuration: ' . $key);
        }
        return $value;
    }

    public static function siteName(): string
    {
        return 'Yur Shack';
    }

    public static function supportEmail(): string
    {
        return self::get('MAIL_TO', 'support@yurshack.com') ?? 'support@yurshack.com';
    }

    public static function composerAutoload(): ?string
    {
        $configured = self::get('COMPOSER_AUTOLOAD');
        $candidates = array_filter([
            $configured,
            dirname(__DIR__) . '/vendor/autoload.php',
            dirname(__DIR__, 2) . '/vendor/autoload.php',
            dirname(__DIR__, 3) . '/public_html/vendor/autoload.php',
        ]);
        foreach ($candidates as $path) {
            if (is_string($path) && is_readable($path)) {
                return $path;
            }
        }
        return null;
    }
}

AppConfig::load();
