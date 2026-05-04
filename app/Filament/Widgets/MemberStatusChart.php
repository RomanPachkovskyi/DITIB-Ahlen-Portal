<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\ChartWidget;

class MemberStatusChart extends ChartWidget
{
    protected ?string $heading = 'Mitgliederstatus';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $active = Member::where('status', 'active')->count();
        $pending = Member::where('status', 'pending')->count();
        $inactive = Member::where('status', 'inactive')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => [$active, $pending, $inactive],
                    'backgroundColor' => [
                        '#10b981', // green (active)
                        '#f59e0b', // amber (pending)
                        '#ef4444', // red (inactive)
                    ],
                ],
            ],
            'labels' => ['Aktiv', 'Ausstehend', 'Inaktiv'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
