@php
    use App\Support\SystemInfo;

    $align = $align ?? 'center';
@endphp

<div
    class="ditib-system-info ditib-system-info-{{ $align }}"
    style="font-size: 11px; color: #9ca3af; line-height: 1.6; text-align: {{ $align === 'right' ? 'right' : 'center' }};"
>
    {{ SystemInfo::version() }} - Update: {{ SystemInfo::updatedAt() }} - by
    <a
        href="https://munas.online/"
        target="_blank"
        rel="noopener noreferrer"
        style="color: #9ca3af; text-decoration: underline;"
    >Munas-Print</a>
</div>
