<div>
@if ($submitted)
    <div class="text-center py-16">
        <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Antrag eingegangen!</h1>
        <p class="text-gray-600 mb-1">Vielen Dank, <strong>{{ $full_name }}</strong>.</p>
        @if ($member_number)
            <p class="text-gray-600 mb-1">Ihre Mitgliedsnummer: <strong class="text-teal-700">{{ $member_number }}</strong></p>
        @endif
        <p class="text-gray-600 mb-6">Ihr Mitgliedsantrag wurde erfolgreich übermittelt.<br>Sie erhalten eine Bestätigung an <strong>{{ $email }}</strong>.</p>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-sm mx-auto text-sm text-yellow-800">
            Ihr Antrag wird geprüft. Nach der Genehmigung erhalten Sie Ihren Mitgliedsausweis.
        </div>
    </div>
@else
    {{-- Fortschrittsanzeige --}}
    <div class="mb-10">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2 text-center sm:text-left tracking-tight">Mitgliedsantrag</h1>
        <p class="text-gray-500 text-sm text-center sm:text-left font-medium">DITIB - Türkisch Islamische Gemeinde zu Ahlen e.V.</p>

        <div class="mt-10 relative">
            {{-- Connecting Line (Background) --}}
            <div class="absolute top-5 left-0 w-full h-1 bg-gray-100 rounded-full" aria-hidden="true"></div>
            
            {{-- Connecting Line (Progress) --}}
            <div class="absolute top-5 left-0 h-1 bg-teal-600 rounded-full transition-all duration-500 ease-in-out" 
                 style="width: {{ ($step - 1) / 3 * 100 }}%"></div>

            <nav class="relative flex justify-between items-start">
                @foreach ([1 => 'Persönliche Daten', 2 => 'Adresse & Kontakt', 3 => 'Beitrag & Zahlung', 4 => 'Unterschrift'] as $n => $label)
                    <div class="flex flex-col items-center group flex-1">
                        {{-- Circle --}}
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold z-10 transition-all duration-300 shadow-sm
                            {{ $step > $n ? 'bg-teal-600 text-white' : ($step === $n ? 'bg-teal-600 text-white ring-4 ring-teal-100' : 'bg-white border-2 border-gray-200 text-gray-400') }}">
                            @if ($step > $n)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $n }}
                            @endif
                        </div>
                        
                        {{-- Label --}}
                        <div class="mt-3 text-center px-1">
                            <span class="block text-[10px] sm:text-xs md:text-sm font-bold uppercase tracking-wider transition-colors duration-300
                                {{ $step >= $n ? 'text-teal-800' : 'text-gray-400' }}">
                                @php
                                    $mobileLabels = [1 => 'Daten', 2 => 'Kontakt', 3 => 'Zahlung', 4 => 'Sign'];
                                @endphp
                                <span class="hidden sm:inline">{{ $label }}</span>
                                <span class="sm:hidden">{{ $mobileLabels[$n] }}</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">

        {{-- STEP 1: Persönliche Daten --}}
        @if ($step === 1)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Persönliche Daten</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vor- und Nachname *</label>
                    <input wire:model.live="full_name" type="text" placeholder="Max Mustermann"
                        x-on:input="this.value = this.value.replace(/[0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('full_name') border-red-400 @enderror">
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geburtsdatum *</label>
                    <input wire:model.blur="birth_date" type="date"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('birth_date') border-red-400 @enderror">
                    @error('birth_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Geburtsort</label>
                    <input wire:model.live.debounce.500ms="birth_place" type="text" placeholder="Berlin"
                        x-on:input="this.value = this.value.replace(/[0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('birth_place') border-red-400 @enderror">
                    @error('birth_place') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Staatsangehörigkeit</label>
                    <input wire:model.live.debounce.500ms="staatsangehoerigkeit" type="text" placeholder="Deutsch"
                        x-on:input="this.value = this.value.replace(/[0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('staatsangehoerigkeit') border-red-400 @enderror">
                    @error('staatsangehoerigkeit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Anzahl der Familienangehörigen</label>
                    <input wire:model.blur="familienangehoerige" type="number" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('familienangehoerige') border-red-400 @enderror">
                    @error('familienangehoerige') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beruf</label>
                    <input wire:model.live.debounce.500ms="beruf" type="text" placeholder="Ingenieur"
                        x-on:input="this.value = this.value.replace(/[0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('beruf') border-red-400 @enderror">
                    @error('beruf') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heimatstadt</label>
                    <input wire:model.live.debounce.500ms="heimatstadt" type="text" placeholder="Ankara"
                        x-on:input="this.value = this.value.replace(/[0-9]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('heimatstadt') border-red-400 @enderror">
                    @error('heimatstadt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Cenaze Fonu --}}
                <div class="sm:col-span-2 border border-gray-100 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-700 mb-2">Mitglied des Bestattungsinstituts (Cenaze Fonu)</p>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="cenaze_fonu" type="radio" value="1"
                                class="text-teal-600">
                            <span class="text-sm">Ja</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="cenaze_fonu" type="radio" value="0"
                                class="text-teal-600">
                            <span class="text-sm">Nein</span>
                        </label>
                    </div>
                    @if ($cenaze_fonu)
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nr.</label>
                            <input wire:model.blur="cenaze_fonu_nr" type="text" placeholder="Nummer"
                                class="w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    @endif
                </div>

                {{-- Gemeinderegister --}}
                <div class="sm:col-span-2 border border-gray-100 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-700 mb-2">Im Gemeinderegister eingetragen</p>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="gemeinderegister" type="radio" value="1"
                                class="text-teal-600">
                            <span class="text-sm">Ja</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="gemeinderegister" type="radio" value="0"
                                class="text-teal-600">
                            <span class="text-sm">Nein</span>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        {{-- STEP 2: Adresse & Kontakt --}}
        @if ($step === 2)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Adresse & Kontakt</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Straße und Hausnummer *</label>
                    <input wire:model.blur="street" type="text" placeholder="Musterstraße 1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('street') border-red-400 @enderror">
                    @error('street') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- PLZ mit Autocomplete --}}
                <div class="relative" x-data="{}" x-on:click.outside="$wire.closePlzDropdown()">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postleitzahl *</label>
                    <input wire:model.live.debounce.250ms="postal_code"
                        type="text"
                        placeholder="59229"
                        inputmode="numeric"
                        maxlength="5"
                        autocomplete="off"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,5)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('postal_code') border-red-400 @enderror">
                    @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                    {{-- Dropdown Suggestions --}}
                    @if ($showPlzDropdown && count($plzSuggestions) > 0)
                        <div style="position:absolute; z-index:9999; left:0; right:0; top:100%; margin-top:4px;
                                    background:#fff; border:1px solid #e5e7eb; border-radius:12px;
                                    box-shadow:0 10px 25px rgba(0,0,0,.12);
                                    max-height:220px; overflow-y:scroll;">
                            <div style="padding:6px 12px 4px; font-size:11px; color:#9ca3af; border-bottom:1px solid #f3f4f6; font-weight:500;">
                                {{ count($plzSuggestions) }} Ergebnisse
                            </div>
                            @foreach ($plzSuggestions as $s)
                                <button
                                    wire:click="selectPlz('{{ $s['plz'] }}', '{{ addslashes($s['ort']) }}', '{{ addslashes($s['bundesland']) }}')"
                                    type="button"
                                    style="display:flex; align-items:center; gap:10px; width:100%; text-align:left;
                                           padding:8px 12px; font-size:13px; border:none; background:transparent;
                                           border-bottom:1px solid #f9fafb; cursor:pointer;"
                                    onmouseover="this.style.background='#f0fdfa'"
                                    onmouseout="this.style.background='transparent'">
                                    <span style="font-family:monospace; font-weight:700; color:#0d9488; min-width:50px;">{{ $s['plz'] }}</span>
                                    <span style="color:#1f2937; flex:1;">{{ $s['ort'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ort *</label>
                    <input wire:model.blur="city" type="text" placeholder="Ahlen"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('city') border-red-400 @enderror">
                    @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bundesland *</label>
                    <input wire:model.blur="state" type="text" placeholder="Nordrhein-Westfalen"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('state') border-red-400 @enderror">
                    @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail *</label>
                    <input wire:model.blur="email" type="email" placeholder="name@beispiel.de"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-400 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                    <input wire:model.blur="phone" type="tel" placeholder="+49 2382 ..."
                        oninput="this.value = this.value.replace(/[a-zA-ZäöüÄÖÜа-яА-Я]/g, '')"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-400 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- STEP 3: Beitrag & Zahlung --}}
        @if ($step === 3)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Beitrag & Zahlungsweise</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monatlicher Mitgliedsbeitrag (€) *</label>
                    <input wire:model.blur="monatsbeitrag" type="number" min="25" step="0.01"
                        x-on:input="if(parseFloat(this.value) < 25 && this.value !== '') this.setCustomValidity('Mindestbetrag 25 €'); else this.setCustomValidity('');"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('monatsbeitrag') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">Mindestbetrag: 25,00 €</p>
                    @error('monatsbeitrag') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Zahlungsweise *</label>
                    <select wire:model.live="zahlungsart"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('zahlungsart') border-red-400 @enderror">
                        <option value="barzahlung">Barzahlung</option>
                        <option value="lastschrift">Lastschrift</option>
                        <option value="dauerauftrag">Dauerauftrag</option>
                    </select>
                    @error('zahlungsart') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if (in_array($zahlungsart, ['lastschrift', 'dauerauftrag']))
                    <div class="sm:col-span-2">
                        <div class="border-t border-gray-100 pt-4 mt-2">
                            <p class="text-sm font-medium text-gray-700 mb-3">Kontodaten</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kontoinhaber *</label>
                                    <input wire:model.blur="kontoinhaber" type="text" placeholder="Max Mustermann"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 @error('kontoinhaber') border-red-400 @enderror">
                                    @error('kontoinhaber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kreditinstitut</label>
                                    <input wire:model.blur="kreditinstitut" type="text" placeholder="Deutsche Bank"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">IBAN *</label>
                                    <input wire:model.blur="iban" type="text" placeholder="DE00 0000 0000 0000 0000 00"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500 @error('iban') border-red-400 @enderror">
                                    @error('iban') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">BIC</label>
                                    <input wire:model.blur="bic" type="text" placeholder="DEUTDEDB"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- STEP 4: Unterschrift & Zustimmung --}}
        @if ($step === 4)
            <h2 class="text-lg font-semibold text-gray-800 mb-5">Unterschrift & Zustimmung</h2>

            <div class="mb-5"
                x-data="{
                    drawing: false,
                    hasDrawn: false,
                    ctx: null,
                    init() {
                        this.$nextTick(() => {
                            const canvas = this.$refs.canvas;
                            const rect = canvas.getBoundingClientRect();
                            canvas.width = Math.floor(rect.width);
                            canvas.height = 160;
                            this.ctx = canvas.getContext('2d');
                            this.ctx.strokeStyle = '#1e293b';
                            this.ctx.lineWidth = 2;
                            this.ctx.lineCap = 'round';
                            this.ctx.lineJoin = 'round';
                        });
                    },
                    getPos(e) {
                        const rect = this.$refs.canvas.getBoundingClientRect();
                        const src = e.touches ? e.touches[0] : e;
                        return { x: src.clientX - rect.left, y: src.clientY - rect.top };
                    },
                    start(e) {
                        this.drawing = true;
                        this.ctx.beginPath();
                        const p = this.getPos(e);
                        this.ctx.moveTo(p.x, p.y);
                    },
                    move(e) {
                        if (!this.drawing) return;
                        const p = this.getPos(e);
                        this.ctx.lineTo(p.x, p.y);
                        this.ctx.stroke();
                        this.hasDrawn = true;
                    },
                    stop() {
                        if (!this.drawing) return;
                        this.drawing = false;
                        if (this.hasDrawn) {
                            const dataURL = this.$refs.canvas.toDataURL('image/png');
                            $wire.set('unterschrift', dataURL);
                        }
                    },
                    clear() {
                        this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
                        this.hasDrawn = false;
                        $wire.set('unterschrift', '');
                    }
                }"
                x-init="init()">

                <label class="block text-sm font-medium text-gray-700 mb-2">Unterschrift *</label>
                <div class="border-2 border-gray-300 rounded-lg bg-white cursor-crosshair @error('unterschrift') border-red-400 @enderror">
                    <canvas
                        x-ref="canvas"
                        @mousedown="start($event)"
                        @mousemove="move($event)"
                        @mouseup="stop()"
                        @mouseleave="stop()"
                        @touchstart.prevent="start($event)"
                        @touchmove.prevent="move($event)"
                        @touchend="stop()"
                        style="display:block; touch-action:none;">
                    </canvas>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <p class="text-xs text-gray-400">Zeichnen Sie Ihre Unterschrift oben</p>
                    <button type="button" @click="clear()" class="text-xs text-red-400 hover:text-red-600">Löschen</button>
                </div>
                @error('unterschrift') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-3 mb-6">
                @if (in_array($zahlungsart, ['lastschrift', 'dauerauftrag']))
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input wire:model.blur="sepa_zustimmung" type="checkbox"
                            class="mt-0.5 w-4 h-4 text-teal-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">
                            Ich erteile das <strong>SEPA-Lastschriftmandat</strong> und ermächtige DITIB Ahlen,
                            den monatlichen Mitgliedsbeitrag von meinem Konto einzuziehen.
                        </span>
                    </label>
                @endif

                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model.blur="dsgvo_zustimmung" type="checkbox"
                        class="mt-0.5 w-4 h-4 text-teal-600 border-gray-300 rounded @error('dsgvo_zustimmung') border-red-400 @enderror">
                    <span class="text-sm text-gray-700">
                        Ich habe die <strong>Datenschutzerklärung</strong> gelesen und stimme der
                        Verarbeitung meiner Daten gemäß DSGVO zu. *
                    </span>
                </label>
                @error('dsgvo_zustimmung') <p class="text-red-500 text-xs ml-7">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Navigation --}}
        <div class="flex justify-between items-center mt-6 pt-5 border-t border-gray-100">
            @if ($step > 1)
                <button wire:click="prevStep" type="button"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Zurück
                </button>
            @else
                <div></div>
            @endif

            @if ($step < 4)
                <button wire:click="nextStep" type="button"
                    class="px-6 py-2 text-sm font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                    Weiter →
                </button>
            @else
                <button wire:click="submit" type="button"
                    wire:loading.attr="disabled"
                    class="px-6 py-2 text-sm font-semibold bg-teal-600 text-white rounded-lg hover:bg-teal-700 disabled:opacity-50">
                    <span wire:loading.remove>Antrag absenden</span>
                    <span wire:loading>Wird gesendet…</span>
                </button>
            @endif
        </div>
    </div>
@endif

</div>
