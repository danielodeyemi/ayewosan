<?php

namespace App\Support;

class EnvEditor
{
    /**
     * Get an env value or default
     */
    public static function get(string $key, $default = null)
    {
        $value = env($key);

        return is_null($value) ? $default : $value;
    }

    /**
     * Set or update an entry in the .env file
     */
    public static function set(string $key, $value): bool
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath) || !is_writable($envPath)) {
            return false;
        }

        $escaped = self::escapeValue($value);

        $contents = file_get_contents($envPath);

        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $key . '=' . $escaped, $contents);
        } else {
            // Append new entry
            $contents .= "\n" . $key . '=' . $escaped . "\n";
        }

        return (bool) file_put_contents($envPath, $contents);
    }

    /**
     * Escape value for .env (wrap in quotes if necessary)
     */
    protected static function escapeValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;

        // If value contains spaces or # or starts with a quote, wrap in double quotes
        if (preg_match('/\s|#|=|\\\$/', $value) || $value === '' || preg_match('/^["\']/', $value)) {
            // Escape existing double quotes
            $value = str_replace('"', '\\"', $value);
            return '"' . $value . '"';
        }

        return $value;
    }
}
