// ==================================================
// AJAX Fetch
// ==================================================

import {
    getPrefetchedPage,
} from './prefetch-series.js';

/*
|------------------------------------------------------------------
| Fetch Page HTML
|------------------------------------------------------------------
*/

export async function fetchPageHtml(
    href,
)
{
    const cached =
        getPrefetchedPage(
            href,
        );

    if (cached) {

        return cached;
    }

    const response =
        await fetch(
            href,
            {
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            },
        );

    if (!response.ok) {

        throw new Error(
            '[AJAX] Request failed',
        );
    }

    return await response.text();
}