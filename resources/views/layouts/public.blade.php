<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitgliedsantrag — DITIB Ahlen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-600 rounded-full flex items-center justify-center text-white font-bold text-sm">D</div>
            <div>
                <div class="font-semibold text-gray-900 text-sm">DITIB Ahlen</div>
                <div class="text-xs text-gray-500">Islamische Gemeinschaft</div>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 py-6">
        © DITIB Ahlen — <a href="https://ditib-ahlen-projekte.de" class="underline">ditib-ahlen-projekte.de</a>
    </footer>

    @livewireScripts
</body>
</html>
