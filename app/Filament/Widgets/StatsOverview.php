<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Offene Anträge', Member::where('status', 'pending')->count())
                ->description('Warten auf Überprüfung')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Mitglieder Gesamt', Member::count())
                ->description('Alle registrierten Personen')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
            Stat::make('Monatliche Einnahmen', '€ ' . number_format(Member::sum('monatsbeitrag'), 2, ',', '.'))
                ->description('Gesamtsumme der Beiträge')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
