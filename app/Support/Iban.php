<?php

namespace App\Support;

class Iban
{
    public static function normalize(?string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value ?? ''));
    }

    public static function format(?string $value): string
    {
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return '';
        }

        $prefix = substr($normalized, 0, 2);
        $checkDigits = substr($normalized, 2, 2);
        $account = substr($normalized, 4);
        $parts = array_filter([$prefix, $checkDigits], fn (string $part) => $part !== '');

        foreach (str_split($account, 4) as $chunk) {
            if ($chunk !== '') {
                $parts[] = $chunk;
            }
        }

        return implode(' ', $parts);
    }

    public static function isValidStructure(?string $value): bool
    {
        $normalized = self::normalize($value);

        return (bool) preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$/', $normalized);
    }
}
