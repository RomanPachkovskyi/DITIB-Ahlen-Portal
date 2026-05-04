<x-mail::message>
# Vielen Dank für Ihren Mitgliedsantrag!

Sehr geehrte(r) **{{ $member->full_name }}**,

wir haben Ihren Antrag auf Mitgliedschaft bei der DITIB Ahlen erfolgreich erhalten. 
Ihr Antrag befindet sich derzeit in Bearbeitung. 

**Ihre vorläufige Mitgliedsnummer:** {{ $member->member_number }}

Wir werden uns in Kürze mit Ihnen in Verbindung setzen, sobald Ihr Antrag geprüft wurde.

Sollten Sie Fragen haben, können Sie auf diese E-Mail antworten.

Mit freundlichen Grüßen,<br>
Ihr Team von {{ config('app.name') }}
</x-mail::message>
