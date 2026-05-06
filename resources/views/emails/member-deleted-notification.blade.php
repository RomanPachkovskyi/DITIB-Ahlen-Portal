<x-mail::message>
# Ihr Mitgliedsdatensatz wurde gelöscht

Sehr geehrte(r) **{{ $member->full_name }}**,

Ihr Mitgliedsdatensatz bei der DITIB Ahlen wurde gelöscht.

**Mitgliedsnummer:** {{ $member->member_number }}

Wenn diese Löschung aus Ihrer Sicht nicht korrekt ist oder Sie Fragen dazu haben, kontaktieren Sie uns bitte direkt.

Mit freundlichen Grüßen,<br>
Ihr Team von {{ config('app.name') }}
</x-mail::message>
