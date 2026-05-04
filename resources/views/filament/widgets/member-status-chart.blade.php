<x-filament-widgets::widget>
    <x-filament::section :heading="$this->getHeading()">
        <div class="flex flex-col items-center justify-center relative py-4">
            <div class="relative" style="width: 100%; max-width: 250px;">
                {{-- Center Text --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="z-index: 10;">
                    <span class="text-3xl font-bold text-gray-900" id="chart-center-total">
                        {{ array_sum($this->getCachedData()['datasets'][0]['data']) }}
                    </span>
                    <span class="text-[10px] uppercase tracking-wider text-gray-500 font-semibold">Gesamt</span>
                </div>

                {{-- The Chart --}}
                @php
                    $chartData = $this->getCachedData();
                @endphp
                
                <canvas
                    x-data="{
                        chart: null,
                        init() {
                            if (typeof Chart === 'undefined') return;
                            
                            this.chart = new Chart($el, {
                                type: 'doughnut',
                                data: {
                                    labels: @js($chartData['labels']),
                                    datasets: @js($chartData['datasets'])
                                },
                                options: {
                                    cutout: '65%',
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'bottom',
                                            labels: {
                                                font: { size: 13, weight: 'bold' },
                                                padding: 20
                                            },
                                            onClick: (e, legendItem, legend) => {
                                                const index = legendItem.index;
                                                const ci = legend.chart;
                                                if (ci.isDatasetVisible(0)) {
                                                    const meta = ci.getDatasetMeta(0);
                                                    meta.data[index].hidden = !meta.data[index].hidden;
                                                    ci.update();
                                                    
                                                    // Update total count
                                                    let total = 0;
                                                    ci.data.datasets[0].data.forEach((val, i) => {
                                                        if (!ci.getDatasetMeta(0).data[i].hidden) {
                                                            total += val;
                                                        }
                                                    });
                                                    document.getElementById('chart-center-total').innerText = total;
                                                }
                                            }
                                        },
                                        tooltip: { enabled: true }
                                    },
                                    maintainAspectRatio: false
                                }
                            });
                        }
                    }"
                    wire:ignore
                    style="height: 250px;"
                ></canvas>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
