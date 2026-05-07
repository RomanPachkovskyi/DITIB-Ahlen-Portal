<?php

namespace App\Support;

class PhoneNumber
{
    public static function validationMessage(): string
    {
        return 'Bitte geben Sie die Telefonnummer mit Vorwahl ein, z.B. 02382 123456, 2382 123456 oder +49 2382 123456.';
    }

    public static function normalize(?string $value): string
    {
        $input = trim($value ?? '');

        if ($input === '') {
            return '';
        }

        $input = preg_replace('/^\s*00/', '+', $input);
        $startsWithPlus = str_starts_with($input, '+');
        $digits = preg_replace('/\D+/', '', $input);

        if ($digits === '') {
            return '';
        }

        if ($startsWithPlus) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+49' . substr($digits, 1);
        }

        if (self::looksLikeGermanNumberWithoutLeadingZero($digits)) {
            return '+49' . $digits;
        }

        return '';
    }

    public static function format(?string $value): string
    {
        $input = trim($value ?? '');
        $normalized = self::normalize($value);

        if ($normalized === '') {
            return $input;
        }

        $digits = substr($normalized, 1);

        if (str_starts_with($digits, '49')) {
            return self::formatGerman($digits);
        }

        return self::formatInternational($digits);
    }

    public static function isValid(?string $value): bool
    {
        $normalized = self::normalize($value);

        return (bool) preg_match('/^\+[1-9][0-9]{7,14}$/', $normalized);
    }

    private static function formatGerman(string $digits): string
    {
        $national = substr($digits, 2);

        if ($national === '') {
            return '+49';
        }

        if (preg_match('/^(15|16|17)/', $national) === 1) {
            $provider = substr($national, 0, 3);
            $subscriber = substr($national, 3);

            return trim('+49 ' . $provider . ' ' . self::chunkSubscriber($subscriber));
        }

        $areaLength = match (true) {
            str_starts_with($national, '30'),
            str_starts_with($national, '40'),
            str_starts_with($national, '69'),
            str_starts_with($national, '89') => 2,
            strlen($national) >= 9 => 4,
            strlen($national) >= 7 => 3,
            default => 2,
        };

        $areaCode = substr($national, 0, $areaLength);
        $subscriber = substr($national, $areaLength);

        return trim('+49 ' . $areaCode . ' ' . self::chunkLandlineSubscriber($subscriber));
    }

    private static function formatInternational(string $digits): string
    {
        $countryCode = self::knownCountryCode($digits);

        if ($countryCode === null) {
            $countryCode = substr($digits, 0, min(3, strlen($digits)));
        }

        $subscriber = substr($digits, strlen($countryCode));

        return trim('+' . $countryCode . ' ' . self::chunkSubscriber($subscriber));
    }

    private static function knownCountryCode(string $digits): ?string
    {
        foreach (['90', '31', '32', '33', '34', '39', '43', '44', '48', '49', '1'] as $code) {
            if (str_starts_with($digits, $code)) {
                return $code;
            }
        }

        return null;
    }

    private static function looksLikeGermanNumberWithoutLeadingZero(string $digits): bool
    {
        if (str_starts_with($digits, '49')) {
            return false;
        }

        if (strlen($digits) < 8 || strlen($digits) > 13) {
            return false;
        }

        if (preg_match('/^(15|16|17)[0-9]{7,10}$/', $digits) === 1) {
            return true;
        }

        return preg_match('/^[2-9][0-9]{1,5}[0-9]{4,10}$/', $digits) === 1;
    }

    private static function chunkSubscriber(string $subscriber): string
    {
        if ($subscriber === '') {
            return '';
        }

        $length = strlen($subscriber);

        if ($length <= 4) {
            return $subscriber;
        }

        if ($length <= 8) {
            return trim(substr($subscriber, 0, $length - 4) . ' ' . substr($subscriber, -4));
        }

        $prefix = substr($subscriber, 0, -4);
        $last = substr($subscriber, -4);
        $parts = str_split($prefix, 3);
        $parts[] = $last;

        return implode(' ', array_filter($parts, fn (string $part) => $part !== ''));
    }

    private static function chunkLandlineSubscriber(string $subscriber): string
    {
        if (strlen($subscriber) <= 8) {
            return $subscriber;
        }

        return self::chunkSubscriber($subscriber);
    }
}
