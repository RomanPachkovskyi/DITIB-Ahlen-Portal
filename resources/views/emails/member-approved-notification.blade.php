<x-mail::message>
# Ihr Mitgliedsantrag wurde angenommen

Sehr geehrte(r) **{{ $member->full_name }}**,

Ihr Antrag auf Mitgliedschaft bei der DITIB Ahlen wurde angenommen.

**Ihre Mitgliedsnummer:** {{ $member->member_number }}

Willkommen in unserer Gemeinde.

Mit freundlichen Grüßen,<br>
Ihr Team von {{ config('app.name') }}
</x-mail::message>
