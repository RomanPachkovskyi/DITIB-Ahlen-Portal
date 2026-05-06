<x-mail::message>
# Mitgliedsdatensatz gelöscht

@php
    $statusLabel = match ($member->status) {
        'pending' => 'Ausstehend',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        default => $member->status,
    };
@endphp

Ein Mitgliedsdatensatz wurde im Portal gelöscht.

**Details zum gelöschten Datensatz:**
- **Name:** {{ $member->full_name }}
- **Mitgliedsnummer:** {{ $member->member_number }}
- **E-Mail:** {{ $member->email }}
- **Telefon:** {{ $member->phone }}
- **Status vor Löschung:** {{ $statusLabel }}
- **Gelöscht am:** {{ now()->format('d.m.Y H:i') }}

Diese Nachricht dient als einfache Löschbestätigung, bis ein vollständiges Audit-Log umgesetzt wird.

Mit freundlichen Grüßen,<br>
System {{ config('app.name') }}
</x-mail::message>
