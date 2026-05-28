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

const CONTENT_SELECTOR =
    '.app-content';

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
        || html.trim() === ''
    ) {
        return false;
    }

    const documentHtml =
        new DOMParser()
            .parseFromString(
                html,
                'text/html',
            );

    return Boolean(
        documentHtml.querySelector(
            CONTENT_SELECTOR,
        ),
    );
}

// =========================================
// FETCH PAGE
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
                    signal:
                        options.signal,

                    headers:
                    {
                        Accept:
                            'text/html',
                    },
                },
            );

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            !isValidHtml(
                html,
            )
        ) {

            throw new Error(
                'Invalid router HTML',
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