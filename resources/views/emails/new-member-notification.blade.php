<x-mail::message>
# Neuer Mitgliedsantrag eingegangen

Ein neuer Mitgliedsantrag wurde soeben über das Online-Formular eingereicht.

**Details zum Antrag:**
- **Name:** {{ $member->full_name }}
- **Mitgliedsnummer:** {{ $member->member_number }}
- **E-Mail:** {{ $member->email }}
- **Telefon:** {{ $member->phone }}
- **Datum:** {{ $member->created_at->format('d.m.Y H:i') }}

Bitte prüfen Sie den Antrag im Administrationsbereich.

<x-mail::button :url="route('filament.admin.resources.members.view', $member)">
Antrag ansehen
</x-mail::button>

Mit freundlichen Grüßen,<br>
System {{ config('app.name') }}
</x-mail::message>
