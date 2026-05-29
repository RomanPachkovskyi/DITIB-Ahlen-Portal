<x-mail::message>
# Zugang zum Mitgliedskonto

Sie haben einen Zugangslink für Ihr DITIB Ahlen Mitgliedskonto angefordert.

<x-mail::button :url="$loginUrl">
Mitgliedskonto öffnen
</x-mail::button>

Der Link ist {{ $expiresInMinutes }} Minuten gültig und kann nur einmal verwendet werden.

Falls Sie diesen Zugang nicht angefordert haben, können Sie diese E-Mail ignorieren.

Mit freundlichen Grüßen,<br>
Ihr Team von {{ config('app.name') }}
</x-mail::message>
