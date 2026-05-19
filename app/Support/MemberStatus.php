<?php

namespace App\Support;

class MemberStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING => 'Neu',
            self::PROCESSING => 'Verarbeitung',
            self::ACTIVE => 'Aktiv',
            self::INACTIVE => 'Inaktiv',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            self::PENDING => 'heroicon-m-sparkles',
            self::PROCESSING => 'heroicon-m-arrow-path',
            self::ACTIVE => 'heroicon-m-check-circle',
            self::INACTIVE => 'heroicon-m-x-circle',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function colors(): array
    {
        return [
            self::PENDING => 'warning',
            self::PROCESSING => 'warning',
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function adminActionLabels(): array
    {
        return [
            self::ACTIVE => 'Als Aktiv markieren',
            self::INACTIVE => 'Als Inaktiv markieren',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function adminBulkActionLabels(): array
    {
        return [
            self::ACTIVE => 'Auf Aktiv setzen',
            self::INACTIVE => 'Auf Inaktiv setzen',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function openStatuses(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
        ];
    }

    public static function label(?string $status): string
    {
        return self::labels()[$status] ?? (string) $status;
    }

    public static function icon(string $status): string
    {
        return self::icons()[$status] ?? 'heroicon-m-question-mark-circle';
    }

    public static function color(string $status): string
    {
        return self::colors()[$status] ?? 'gray';
    }
}
