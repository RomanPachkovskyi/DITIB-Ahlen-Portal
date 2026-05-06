<style>
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

    .ditib-status-action-active :is(svg, span),
    .ditib-status-action-pending :is(svg, span) {
        color: inherit !important;
    }
</style>
