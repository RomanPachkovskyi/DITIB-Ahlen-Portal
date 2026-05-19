<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class SystemInfo
{
    public const FALLBACK_NAME = 'DiTiB-Registrierungssystem';

    public static function label(): string
    {
        return sprintf(
            '%s - Update: %s - by Munas-Print',
            self::version(),
            self::updatedAt()
        );
    }

    public static function name(): string
    {
        return (string) (self::data()['name'] ?? self::FALLBACK_NAME);
    }

    public static function version(): string
    {
        $data = self::data();

        return sprintf('v%d.%03d', (int) ($data['major'] ?? 1), (int) ($data['minor'] ?? 0));
    }

    public static function updatedAt(): string
    {
        $date = (string) (self::data()['updated_at'] ?? now()->toDateString());

        return CarbonImmutable::parse($date)->format('d.m.Y');
    }

    /**
     * @return array{name?: string, major?: int, minor?: int, updated_at?: string}
     */
    private static function data(): array
    {
        $path = base_path('config/system-version.json');

        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }
}
