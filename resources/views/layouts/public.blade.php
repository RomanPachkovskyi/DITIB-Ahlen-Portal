<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitgliedsantrag — DITIB Ahlen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="py-8">
        <div class="max-w-3xl mx-auto px-4 flex flex-col items-center text-center">
            <img src="{{ asset('images/ditib_ahlen_logo.png') }}" alt="DITIB Ahlen Logo" class="w-32 h-auto mb-3">
            <div class="font-bold text-gray-900 text-lg tracking-wide">DITIB Ahlen</div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="max-w-3xl mx-auto px-4 py-8">
        <div class="text-center text-[10px] sm:text-xs text-gray-400 leading-relaxed mb-4">
            <p>Rottmannstr. 62 · 59229 Ahlen · Tel: 02382-61599 · Fax: 702397</p>
            <p>Gründung 17.02.1978 · Amtsgericht Ahlen, VR-50380 · Finanzamt Beckum ST NR: 304 / 5861 / 0097</p>
            <p>DITIB Listennr.: 1005 · Bankverbindung Sparkasse Münsterland Ost · IBAN: DE 42 4005 0150 0068 0009 59</p>
        </div>
        <div class="text-center text-xs text-gray-400 border-t border-gray-100 pt-6">
            © DITIB Ahlen — <a href="https://ditib-ahlen-projekte.de" target="_blank" rel="noopener noreferrer" class="underline hover:text-teal-600 transition-colors">ditib-ahlen-projekte.de</a>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
