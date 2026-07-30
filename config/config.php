<?php

define('BASE_PATH', dirname(__DIR__));

// Minimal .env loader (no Composer/vendor dependency)
function load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

load_env(BASE_PATH . '/.env');

define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Always report + log every error level. display_errors is the only thing
// that should differ by environment — silencing error_reporting() in
// production would stop issues from reaching the server log too, not just
// hide them from visitors.
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');

date_default_timezone_set('UTC');

define('SITE_NAME', 'Aashish Rai');
