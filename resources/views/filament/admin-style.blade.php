@php
    use App\Support\BrandColors;
@endphp

<style>
    :root {
        --ditib-brand-primary: {{ BrandColors::PRIMARY_HEX }};
        --ditib-brand-primary-hover: {{ BrandColors::PRIMARY_HOVER_CSS_VAR }};
        --ditib-brand-on-primary: {{ BrandColors::ON_PRIMARY_HEX }};
    }

    .fi-btn.fi-color-primary,
    .fi-btn.ditib-brand-primary-button {
        --bg: var(--ditib-brand-primary);
        --hover-bg: var(--ditib-brand-primary-hover);
        --dark-bg: var(--ditib-brand-primary);
        --dark-hover-bg: var(--ditib-brand-primary-hover);
        --text: var(--ditib-brand-on-primary);
        --hover-text: var(--ditib-brand-on-primary);
        --dark-text: var(--ditib-brand-on-primary);
        --dark-hover-text: var(--ditib-brand-on-primary);
        background-color: var(--ditib-brand-primary) !important;
        color: var(--ditib-brand-on-primary) !important;
    }

    .fi-btn.fi-color-primary:hover,
    .fi-btn.ditib-brand-primary-button:hover {
        background-color: var(--ditib-brand-primary-hover) !important;
        color: var(--ditib-brand-on-primary) !important;
    }

    .fi-btn.fi-color-primary :is(svg, span),
    .fi-btn.ditib-brand-primary-button :is(svg, span) {
        color: inherit !important;
    }

    .ditib-status-action-active {
        --color-700: oklch(0.527 0.154 150.069);
        --text: oklch(0.527 0.154 150.069);
        --hover-text: oklch(0.527 0.154 150.069);
        color: oklch(0.527 0.154 150.069) !important;
    }

    .ditib-status-action-pending {
        --color-700: oklch(0.555 0.163 48.998);
        --text: oklch(0.555 0.163 48.998);
        --hover-text: oklch(0.555 0.163 48.998);
        color: oklch(0.555 0.163 48.998) !important;
    }

    .ditib-status-action-processing {
        --color-700: oklch(0.555 0.163 48.998);
        --text: oklch(0.555 0.163 48.998);
        --hover-text: oklch(0.555 0.163 48.998);
        color: oklch(0.555 0.163 48.998) !important;
    }

    .ditib-status-action-active :is(svg, span),
    .ditib-status-action-pending :is(svg, span),
    .ditib-status-action-processing :is(svg, span) {
        color: inherit !important;
    }

    .fi-ta-search-field {
        width: min(24rem, calc(100vw - 2rem));
    }
</style>
