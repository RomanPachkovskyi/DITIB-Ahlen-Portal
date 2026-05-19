<?php

namespace App\Support;

class Instagram
{
    public static function normalize(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', '', $value);
        $value = ltrim($value, '@');

        if (preg_match('~^(www\.)?instagram\.com/~i', $value) === 1) {
            $value = 'https://' . $value;
        }

        if (preg_match('~^https?://~i', $value) === 1) {
            $host = parse_url($value, PHP_URL_HOST);
            $path = parse_url($value, PHP_URL_PATH);

            $host = is_string($host) ? strtolower($host) : null;

            if (($host !== 'instagram.com' && ! str_ends_with((string) $host, '.instagram.com')) || ! is_string($path)) {
                return null;
            }

            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            $value = $segments[0] ?? '';
        }

        $value = trim($value, '@/');

        return self::isValidUsername($value) ? $value : null;
    }

    public static function display(?string $value): string
    {
        $normalized = self::normalize($value);

        return $normalized === null ? '' : '@' . $normalized;
    }

    public static function url(?string $value): ?string
    {
        $normalized = self::normalize($value);

        return $normalized === null ? null : 'https://www.instagram.com/' . $normalized . '/';
    }

    public static function isValid(?string $value): bool
    {
        return trim((string) $value) === '' || self::normalize($value) !== null;
    }

    private static function isValidUsername(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9._]{1,30}$/', $value) === 1;
    }
}
