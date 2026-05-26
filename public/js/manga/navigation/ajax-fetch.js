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

/*
|------------------------------------------------------------------
| Fetch Page HTML
|------------------------------------------------------------------
*/

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
    // Fetch
    // ==============================================

    const response =
        await fetch(
            normalizedUrl,
            {
                signal:
                    options.signal,

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

    if (! response.ok) {

        throw new Error(
            '[AJAX] Request failed',
        );
    }

    return await response.text();
}