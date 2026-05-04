<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use Filament\Widgets\ChartWidget;

class MemberStatusChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Mitgliederstatus (Gesamt: ' . \App\Models\Member::count() . ')';
    }
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
                        '#10b981', // green
                        '#f59e0b', // amber
                        '#ef4444', // red
                    ],
                ],
            ],
            'labels' => [
                "Aktiv ({$active})",
                "Ausstehend ({$pending})",
                "Inaktiv ({$inactive})",
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                        'padding' => 20,
                    ],
                    'onHover' => \Filament\Support\RawJs::make(<<<JS
                        function(event, legendItem, legend) {
                            event.native.target.style.cursor = 'pointer';
                            legend.options.labels.color = '#2563eb'; // blue
                            legend.chart.update();
                        }
                    JS),
                    'onLeave' => \Filament\Support\RawJs::make(<<<JS
                        function(event, legendItem, legend) {
                            event.native.target.style.cursor = 'default';
                            legend.options.labels.color = '#64748b'; // default gray
                            legend.chart.update();
                        }
                    JS),
                ],
            ],
            'cutout' => '50%',
            'hover' => [
                'mode' => null,
            ],
            'animation' => [
                'duration' => 400,
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
