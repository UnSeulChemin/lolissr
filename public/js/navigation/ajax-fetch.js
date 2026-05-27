// =========================================
// AJAX FETCH
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
} from '../core/navigation.js';

import {
    getPrefetchedPage,
} from './prefetch.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const SELECTOR =
    '.ajax-content';

// =========================================
// VALIDATION
// =========================================

function isValidHtml(html)
{
    if (
        typeof html
        !== 'string'
        || html.length === 0
    ) {
        return false;
    }

    const doc =
        new DOMParser()
            .parseFromString(
                html,
                'text/html',
            );

    return Boolean(
        doc.querySelector(
            SELECTOR,
        ),
    );
}

// =========================================
// FETCH
// =========================================

export async function fetchPageHtml(
    href,
    options = {},
)
{
    const url =
        normalizeUrl(
            href,
        );

    // =====================================
    // PREFETCH CACHE
    // =====================================

    const cached =
        getPrefetchedPage(
            url,
        );

    if (cached) {

        debug(
            'FETCH',
            'cache-hit',
            url,
        );

        return cached;
    }

    try {

        const html =
            await request(
                url,
                {
                    responseType:
                        'text',

                    signal:
                        options.signal,

                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        Accept:
                            'text/html',
                    },
                },
            );

        if (
            !isValidHtml(
                html,
            )
        ) {

            throw new Error(
                'Invalid AJAX HTML',
            );
        }

        return html;

    } catch (error) {

        debugError(
            'FETCH',
            error,
        );

        throw error;
    }
}