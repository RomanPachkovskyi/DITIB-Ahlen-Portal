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

    .ditib-audit-log-link .fi-in-text.fi-wrapped:not(.fi-in-text-has-badges.fi-in-text-has-line-breaks) {
        text-align: center;
        white-space: normal;
        overflow-wrap: break-word;
    }

    .ditib-audit-timeline {
        display: grid;
        gap: 0;
    }

    .ditib-audit-timeline-item {
        display: grid;
        grid-template-columns: 1.25rem minmax(0, 1fr);
        column-gap: 0.75rem;
        padding-bottom: 1.25rem;
        position: relative;
    }

    .ditib-audit-timeline-item:last-child {
        padding-bottom: 0;
    }

    .ditib-audit-timeline-marker {
        display: flex;
        justify-content: center;
        position: relative;
    }

    .ditib-audit-timeline-dot {
        background: var(--ditib-brand-primary);
        border-radius: 9999px;
        box-shadow: 0 0 0 6px #d1fae5;
        height: 0.625rem;
        margin-top: 0.375rem;
        width: 0.625rem;
        z-index: 1;
    }

    .ditib-audit-timeline-content {
        min-width: 0;
    }

    .ditib-audit-timeline-meta {
        align-items: center;
        color: #111827;
        display: flex;
        flex-wrap: wrap;
        font-size: 0.75rem;
        font-weight: 600;
        gap: 0.375rem;
        line-height: 1rem;
    }

    .ditib-audit-timeline-actor {
        background: #f4f4f5;
        border-radius: 0.375rem;
        color: #52525b;
        font-size: 11px;
        font-weight: 500;
        line-height: 1rem;
        padding: 0.125rem 0.375rem;
    }

    .ditib-audit-timeline-list {
        color: #6b7280;
        display: grid;
        font-size: 0.75rem;
        gap: 0.125rem;
        line-height: 1.25rem;
        margin-top: 0.25rem;
    }

    .dark .ditib-audit-timeline-dot {
        background: var(--ditib-brand-primary);
        box-shadow: 0 0 0 6px rgba(0, 150, 137, 0.22);
    }

    .dark .ditib-audit-timeline-meta {
        color: #f3f4f6;
    }

    .dark .ditib-audit-timeline-actor {
        background: #1f2937;
        color: #d1d5db;
    }

    .dark .ditib-audit-timeline-list {
        color: #9ca3af;
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
