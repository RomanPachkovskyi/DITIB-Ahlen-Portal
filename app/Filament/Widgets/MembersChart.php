<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\ChartWidget;

class MembersChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected ?string $heading = 'Registrierungen pro Monat';
    protected ?string $pollingInterval = '30s';
    public ?string $filter = '2026';

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $countsByMonth = Member::query()
            ->whereYear('created_at', $activeFilter)
            ->get(['created_at'])
            ->countBy(fn (Member $member) => $member->created_at?->format('m'));

        $counts = collect(range(1, 12))
            ->map(fn (int $month) => $countsByMonth->get(str_pad((string) $month, 2, '0', STR_PAD_LEFT), 0))
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Neue Mitglieder',
                    'data' => $counts,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(20, 184, 166, 0.1)',
                    'borderColor' => '#14b8a6',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
