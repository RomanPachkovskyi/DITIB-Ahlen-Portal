@php
    $iconStyle = 'width:1.25rem;height:1.25rem;flex:0 0 auto;';
    $brand = 'color: var(--ditib-brand-primary, #009689);';
@endphp

<div style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.875rem;line-height:1.5;">
    <p>
        Diese Mitgliedschaft ist derzeit <strong>inaktiv</strong> und kann nicht
        geöffnet oder bearbeitet werden.
    </p>

    <p>
        Den Status können Sie nicht selbst ändern. Für eine Reaktivierung oder
        bei Fragen wenden Sie sich bitte an DITIB Ahlen:
    </p>

    {{-- Filament section handles light/dark theming of the card itself. --}}
    <x-filament::section>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            <a href="tel:+49238261599"
               style="display:flex;align-items:center;gap:0.75rem;font-weight:500;text-decoration:none;{{ $brand }}">
                <x-heroicon-o-phone width="20" height="20" style="{{ $iconStyle }}" />
                <span>02382 / 61599</span>
            </a>
            <a href="mailto:info@ditib-ahlen-projekte.de"
               style="display:flex;align-items:center;gap:0.75rem;font-weight:500;text-decoration:none;word-break:break-all;{{ $brand }}">
                <x-heroicon-o-envelope width="20" height="20" style="{{ $iconStyle }}" />
                <span>info@ditib-ahlen-projekte.de</span>
            </a>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <x-heroicon-o-map-pin width="20" height="20" style="{{ $iconStyle }}" />
                <span>Rottmannstr. 62, 59229 Ahlen</span>
            </div>
        </div>
    </x-filament::section>
</div>
