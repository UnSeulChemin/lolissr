// ==================================================
// AJAX Fetch
// ==================================================

import {
    getPrefetchedPage,
} from './prefetch-series.js';

// ==================================================
// Helpers
// ==================================================

function normalizeUrl(
    href,
)
{
    const url =
        new URL(
            href,
            window.location.origin,
        );

    return (
        url.pathname
        + url.search
    );
}

// ==================================================
// Fetch Page HTML
// ==================================================

export async function fetchPageHtml(
    href,
    options = {},
)
{
    const normalizedUrl =
        normalizeUrl(
            href,
        );

    // ==============================================
    // Cache
    // ==============================================

    const cached =
        getPrefetchedPage(
            normalizedUrl,
        );

    if (cached) {
        return cached;
    }

    // ==============================================
    // Timeout
    // ==============================================

    const timeoutController =
        new AbortController();

    const timeout =
        setTimeout(
            () =>
            {
                timeoutController.abort();
            },
            10000,
        );

    const signal =
        options.signal
            ?? timeoutController.signal;

    try {

        const response =
            await fetch(
                normalizedUrl,
                {
                    signal,

                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        'X-Partial':
                            'true',

                        'Accept':
                            'text/html',
                    },
                },
            );

        if (!response.ok) {

            throw new Error(
                `[AJAX] ${response.status}`,
            );
        }

        return await response.text();

    } finally {

        clearTimeout(
            timeout,
        );
    }
}