<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

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

        $data = Member::select(
            DB::raw('count(id) as total'),
            DB::raw("strftime('%m', created_at) as month")
        )
        ->whereYear('created_at', $activeFilter)
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')
        ->toArray();

        $counts = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $counts[] = $data[$key] ?? 0;
        }

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
