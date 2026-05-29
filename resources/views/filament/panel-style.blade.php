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

    .fi-sc.fi-sc-has-gap {
        gap: calc(var(--spacing) * 3);
    }

    .fi-simple-layout .fi-simple-main {
        overflow: visible;
        position: relative;
    }

    .ditib-auth-system-info {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 100%;
    }

    .ditib-konto-register-link {
        margin-top: calc(var(--spacing) * 3);
        color: #6b7280;
        font-size: 0.875rem;
        line-height: 1.5;
        text-align: center;
    }

    .ditib-konto-register-link a {
        color: var(--ditib-brand-primary);
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    @media (max-width: 640px) {
        .fi-simple-layout .fi-simple-main-ctn {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .fi-simple-layout .fi-simple-main {
            max-width: calc(100vw - 2rem);
            border-radius: 0.75rem;
        }
    }
</style>
