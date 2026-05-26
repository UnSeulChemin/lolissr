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

import {
    config,
} from '../core/config.js';

// =========================================
// Config
// =========================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

const FETCH_TIMEOUT =
    config.ajax.timeout;

// =========================================
// Helpers
// =========================================

function isValidHtmlResponse(
    html,
)
{
    return (
        typeof html
            === 'string'
        && html.includes(
            AJAX_CONTAINER_SELECTOR,
        )
    );
}

function createTimeoutSignal(
    signal,
    timeout = FETCH_TIMEOUT,
)
{
    const controller =
        new AbortController();

    const timeoutId =
        window.setTimeout(
            () =>
            {
                controller.abort(
                    'timeout',
                );
            },
            timeout,
        );

    if (signal)
    {
        signal.addEventListener(
            'abort',
            () =>
            {
                controller.abort();
            },
            {
                once: true,
            },
        );
    }

    return {
        signal:
            controller.signal,

        cleanup:
            () =>
            {
                clearTimeout(
                    timeoutId,
                );
            },
    };
}

// =========================================
// Fetch Page HTML
// =========================================

export async function fetchPageHtml(
    href,
    options = {},
)
{
    const normalizedUrl =
        normalizeUrl(
            href,
        );

    // =====================================
    // Prefetch Cache
    // =====================================

    const cachedPage =
        getPrefetchedPage(
            normalizedUrl,
        );

    if (
        isValidHtmlResponse(
            cachedPage,
        )
    ) {

        debug(
            'FETCH',
            'cache-hit',
            normalizedUrl,
        );

        return cachedPage;
    }

    // =====================================
    // Timeout Signal
    // =====================================

    const {
        signal,
        cleanup,
    } = createTimeoutSignal(
        options.signal,
    );

    try {

        debug(
            'FETCH',
            'request',
            normalizedUrl,
        );

        const html =
            await request(
                normalizedUrl,
                {
                    responseType:
                        'text',

                    signal,

                    headers: {
                        'X-Partial':
                            'true',

                        'Accept':
                            'text/html',
                    },
                },
            );

        if (
            !isValidHtmlResponse(
                html,
            )
        ) {

            throw new Error(
                '[AJAX] Invalid HTML response',
            );
        }

        debug(
            'FETCH',
            'success',
            normalizedUrl,
        );

        return html;

    } catch (error) {

        if (
            error instanceof Error
            && error.name
                === 'AbortError'
        ) {

            debug(
                'FETCH',
                'aborted',
                normalizedUrl,
            );

            throw error;
        }

        debugError(
            'FETCH',
            error,
        );

        throw error;

    } finally {

        cleanup();
    }
}