<?php

namespace App\Support;

use Filament\Support\Colors\Color;

final class BrandColors
{
    public const PRIMARY_HEX = '#009689';
    public const ON_PRIMARY_HEX = '#ffffff';
    public const PRIMARY_HOVER_CSS_VAR = 'var(--color-teal-700, var(--primary-700))';

    /**
     * @return array<int, string>
     */
    public static function primary(): array
    {
        return Color::Teal;
    }
}
