<x-filament-panels::page>
    @php
        $logs = $this->getLogs();
    @endphp

    <div class="ditib-audit-timeline">
        @forelse ($logs as $log)
            @php
                if ($log->event === 'member_updated' && $log->changed_fields) {
                    $items = $log->changed_fields;
                    $items = array_map(fn (string $field): string => $field.' geändert', $items);
                } else {
                    $items = [$log->description];
                }
            @endphp

            <div class="ditib-audit-timeline-item">
                <div class="ditib-audit-timeline-marker">
                    <span class="ditib-audit-timeline-dot"></span>
                </div>

                <div class="ditib-audit-timeline-content">
                    <div class="ditib-audit-timeline-meta">
                        <span>{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                        <span class="ditib-audit-timeline-actor">
                            {{ $log->actorLabel() }}
                        </span>
                    </div>

                    <ul class="ditib-audit-timeline-list">
                        @foreach ($items as $item)
                            <li>- {{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <x-filament::section>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Für diesen Eintrag gibt es noch keine gespeicherte Änderungshistorie.
                </p>
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>
