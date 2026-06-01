<x-mail::message>
# Mitglied hat eigene Daten aktualisiert

Ein Mitglied hat über das Mitgliedskonto eigene Daten geändert. Bitte prüfen Sie die Änderungen im Administrationsbereich.

**Mitglied:**
- **Name:** {{ $member->full_name }}
- **Mitgliedsnummer:** {{ $member->member_number }}
- **E-Mail:** {{ $member->email }}

**Geänderte Felder:**
@foreach ($changes as $change)
- **{{ $change['label'] }}:** {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}
@endforeach

<x-mail::button :url="$adminUrl">
Datensatz im Admin öffnen
</x-mail::button>

Mit freundlichen Grüßen,<br>
System {{ config('app.name') }}
</x-mail::message>
