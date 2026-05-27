// =========================================
// ROUTER FETCH
// =========================================

import {
    request,
} from '../core/http.js';

import {
    normalizeUrl,
} from '../core/navigation.js';

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

function isValidHtml(
    html,
)
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

    try {

        debug(
            'FETCH',
            'network',
            url,
        );

        const html =
            await request(
                url,
                {
                    responseType:
                        'text',

                    signal:
                        options.signal,

                    headers:
                    {
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

        debug(
            'FETCH',
            'success',
            url,
        );

        return html;

    } catch (error) {

        debugError(
            'FETCH',
            error,
        );

        throw error;
    }
}