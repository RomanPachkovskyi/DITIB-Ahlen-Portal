<div>
@if ($submitted)
    {{-- Bestätigungsseite --}}
    <div class="text-center py-16">
        <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Antrag eingegangen!</h1>
        <p class="text-gray-600 mb-1">Vielen Dank, <strong>{{ $full_name }}</strong>.</p>
        <p class="text-gray-600 mb-6">Ihr Mitgliedsantrag wurde erfolgreich übermittelt.<br>Sie erhalten eine Bestätigung an <strong>{{ $email }}</strong>.</p>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-sm mx-auto text-sm text-yellow-800">
            Ihr Antrag wird geprüft. Nach der Genehmigung erhalten Sie Ihren Mitgliedsausweis.
        </div>
    </div>
@else
    {{-- Fortschrittsanzeige --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Mitgliedsantrag</h1>
        <p class="text-gray-500 text-sm">DITIB Ahlen — Islamische Gemeinschaft e.V.</p>

        <div class="flex items-center mt-6 gap-2">
            @foreach ([1 => 'Persönliche Daten', 2 => 'Bankverbindung', 3 => 'Unterschrift'] as $n => $label)
                <div class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                        {{ $step > $n ? 'bg-teal-600 text-white' : ($step === $n ? 'bg-teal-600 text-white ring-4 ring-teal-100' : 'bg-gray-200 text-gray-500') }}">
                        {{ $step > $n ? '✓' : $n }}
                    </div>
                    <span class="text-xs {{ $step === $n ? 'text-teal-700 font-semibold' : 'text-gray-400' }} hidden sm:inline">{{ $label }}</span>
                    @if (!$loop->last)
                        <div class="flex-1 h-px {{ $step > $n ? 'bg-teal-400' : 'bg-gray-200' }} mx-1"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">

        {{-- STEP 1: Persönliche Daten --}}
        @if ($step === 1)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Persönliche Daten</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vor- und Nachname *</label>
                    <input wire:model="full_name" type="text" placeholder="Max Mustermann"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('full_name') border-red-400 @enderror">
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geburtsdatum *</label>
                    <input wire:model="birth_date" type="date"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('birth_date') border-red-400 @enderror">
                    @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Straße und Hausnummer *</label>
                    <input wire:model="street" type="text" placeholder="Musterstraße 1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('street') border-red-400 @enderror">
                    @error('street') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postleitzahl *</label>
                    <input wire:model="postal_code" type="text" placeholder="59227"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('postal_code') border-red-400 @enderror">
                    @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ort *</label>
                    <input wire:model="city" type="text" placeholder="Ahlen"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('city') border-red-400 @enderror">
                    @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bundesland *</label>
                    <input wire:model="state" type="text" placeholder="Nordrhein-Westfalen"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('state') border-red-400 @enderror">
                    @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail *</label>
                    <input wire:model="email" type="email" placeholder="name@beispiel.de"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-400 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefonnummer *</label>
                    <input wire:model="phone" type="tel" placeholder="+49 2382 ..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-400 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- STEP 2: Bankverbindung --}}
        @if ($step === 2)
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Bankverbindung (SEPA)</h2>
            <p class="text-sm text-gray-500 mb-5">Der Jahresbeitrag wird jeweils im September per Lastschrift eingezogen.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jahresbeitrag (€) *</label>
                    <input wire:model="jahresbeitrag" type="number" min="36" step="0.01"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('jahresbeitrag') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">Mindestbetrag: €36,00</p>
                    @error('jahresbeitrag') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kontoinhaber *</label>
                    <input wire:model="kontoinhaber" type="text" placeholder="Max Mustermann"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('kontoinhaber') border-red-400 @enderror">
                    @error('kontoinhaber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">IBAN *</label>
                    <input wire:model="iban" type="text" placeholder="DE00 0000 0000 0000 0000 00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500 @error('iban') border-red-400 @enderror">
                    @error('iban') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BIC</label>
                    <input wire:model="bic" type="text" placeholder="DEUTDEDB"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kreditinstitut</label>
                    <input wire:model="kreditinstitut" type="text" placeholder="Deutsche Bank"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
        @endif

        {{-- STEP 3: Unterschrift & Zustimmung --}}
        @if ($step === 3)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Unterschrift & Zustimmung</h2>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Unterschrift *</label>
                <div class="border-2 border-gray-300 rounded-lg overflow-hidden bg-white @error('unterschrift') border-red-400 @enderror">
                    <canvas id="signature-pad" class="w-full" height="160"></canvas>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <p class="text-xs text-gray-400">Zeichnen Sie Ihre Unterschrift oben</p>
                    <button type="button" onclick="clearSignature()" class="text-xs text-red-400 hover:text-red-600">Löschen</button>
                </div>
                @error('unterschrift') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <input type="hidden" wire:model="unterschrift" id="unterschrift-input">
            </div>

            <div class="space-y-3 mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="sepa_zustimmung" type="checkbox"
                        class="mt-0.5 w-4 h-4 text-teal-600 border-gray-300 rounded @error('sepa_zustimmung') border-red-400 @enderror">
                    <span class="text-sm text-gray-700">
                        Ich erteile das <strong>SEPA-Lastschriftmandat</strong> und ermächtige DITIB Ahlen,
                        den Jahresbeitrag von meinem Konto einzuziehen. *
                    </span>
                </label>
                @error('sepa_zustimmung') <p class="text-red-500 text-xs ml-7">{{ $message }}</p> @enderror

                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="dsgvo_zustimmung" type="checkbox"
                        class="mt-0.5 w-4 h-4 text-teal-600 border-gray-300 rounded @error('dsgvo_zustimmung') border-red-400 @enderror">
                    <span class="text-sm text-gray-700">
                        Ich habe die <strong>Datenschutzerklärung</strong> gelesen und stimme der
                        Verarbeitung meiner Daten gemäß DSGVO zu. *
                    </span>
                </label>
                @error('dsgvo_zustimmung') <p class="text-red-500 text-xs ml-7">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="flex justify-between items-center mt-6 pt-5 border-t border-gray-100">
            @if ($step > 1)
                <button wire:click="prevStep" type="button"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Zurück
                </button>
            @else
                <div></div>
            @endif

            @if ($step < 3)
                <button wire:click="nextStep" type="button"
                    class="px-6 py-2 text-sm font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    Weiter →
                </button>
            @else
                <button wire:click="submit" type="button"
                    wire:loading.attr="disabled"
                    class="px-6 py-2 text-sm font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50">
                    <span wire:loading.remove>Antrag absenden</span>
                    <span wire:loading>Wird gesendet...</span>
                </button>
            @endif
        </div>
    </div>
@endif

@if ($step === 3 && !$submitted)
<script>
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.offsetWidth;

    let drawing = false;
    let hasDrawn = false;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const src = e.touches ? e.touches[0] : e;
        return { x: src.clientX - rect.left, y: src.clientY - rect.top };
    }

    canvas.addEventListener('mousedown', e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove', e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); hasDrawn = true; });
    canvas.addEventListener('mouseup', () => { drawing = false; saveSignature(); });
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvas.addEventListener('touchmove', e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); hasDrawn = true; }, { passive: false });
    canvas.addEventListener('touchend', () => { drawing = false; saveSignature(); });

    function saveSignature() {
        if (!hasDrawn) return;
        const dataURL = canvas.toDataURL('image/png');
        document.getElementById('unterschrift-input').value = dataURL;
        @this.set('unterschrift', dataURL);
    }

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        document.getElementById('unterschrift-input').value = '';
        @this.set('unterschrift', '');
    }
</script>
@endif
</div>
