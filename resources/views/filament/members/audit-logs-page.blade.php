<x-filament-panels::page>
    @php
        $logs = $this->getLogs();
        $emailLogs = $this->getEmailLogs();
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

    <x-filament::section class="mt-6">
        <x-slot name="heading">E-Mail Versand</x-slot>

        @if ($emailLogs->isEmpty())
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Für dieses Mitglied wurden noch keine E-Mails protokolliert.
            </p>
        @else
            <div class="ditib-email-log-table">
                <table>
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>E-Mail Typ</th>
                            <th>Empfänger</th>
                            <th>E-Mail Adresse</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emailLogs as $emailLog)
                            <tr>
                                <td>{{ $emailLog->created_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $emailLog->eventLabel() }}</td>
                                <td>{{ $emailLog->recipientTypeLabel() }}</td>
                                <td>{{ $emailLog->recipient_email }}</td>
                                <td>
                                    <span class="ditib-email-log-badge ditib-email-log-badge--{{ $emailLog->status }}">
                                        {{ $emailLog->status === 'sent' ? 'Gesendet' : 'Fehler' }}
                                    </span>
                                    @if ($emailLog->error_message)
                                        <span class="ditib-email-log-error" title="{{ $emailLog->error_message }}">⚠</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
