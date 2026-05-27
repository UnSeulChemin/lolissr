// =========================================
// CORE : HTTP
// =========================================

import {
    debugError,
} from './debug.js';

// =========================================
// CONFIG
// =========================================

const DEFAULT_TIMEOUT =
    15000;

// =========================================
// CSRF
// =========================================

function getCsrfToken()
{
    return (
        window.csrfToken
        || ''
    );
}

// =========================================
// HEADERS
// =========================================

function buildHeaders(
    custom = {},
)
{
    return {
        'X-Requested-With':
            'XMLHttpRequest',

        'X-CSRF-TOKEN':
            getCsrfToken(),

        ...custom,
    };
}

// =========================================
// TIMEOUT
// =========================================

function createTimeoutController(
    timeout,
)
{
    const controller =
        new AbortController();

    const timer =
        window.setTimeout(
            () =>
            {
                controller.abort(
                    'Request timeout',
                );
            },
            timeout,
        );

    return {
        controller,
        clear:
            () =>
            {
                clearTimeout(
                    timer,
                );
            },
    };
}

// =========================================
// PARSE RESPONSE
// =========================================

async function parseResponse(
    response,
    type,
)
{
    // =====================================
    // TEXT
    // =====================================

    if (
        type === 'text'
    ) {

        return response.text();
    }

    // =====================================
    // JSON
    // =====================================

    const text =
        await response.text();

    if (
        text.trim()
        === ''
    ) {

        return null;
    }

    try {

        return JSON.parse(
            text,
        );

    } catch {

        throw new Error(
            'Invalid JSON response',
        );
    }
}

// =========================================
// HTTP ERROR
// =========================================

function createHttpError(
    response,
    data,
)
{
    const error =
        new Error(
            data?.message
            || `HTTP ${response.status}`,
        );

    error.status =
        response.status;

    error.data =
        data;

    return error;
}

// =========================================
// REQUEST
// =========================================

export async function request(
    url,
    options = {},
)
{
    const responseType =
        options.responseType
        || 'text';

    const timeout =
        options.timeout
        || DEFAULT_TIMEOUT;

    const timeoutController =
        createTimeoutController(
            timeout,
        );

    const signal =
        options.signal
            ? AbortSignal.any([
                options.signal,
                timeoutController.controller.signal,
            ])
            : timeoutController.controller.signal;

    try {

        const response =
            await fetch(
                url,
                {
                    credentials:
                        'same-origin',

                    ...options,

                    signal,

                    headers:
                        buildHeaders(
                            options.headers,
                        ),
                },
            );

        const data =
            await parseResponse(
                response,
                responseType,
            );

        // =================================
        // HTTP ERROR
        // =================================

        if (
            !response.ok
        ) {

            throw createHttpError(
                response,
                data,
            );
        }

        return data;

    } catch (error) {

        debugError(
            'HTTP',
            error,
        );

        if (
            error?.name
            === 'AbortError'
        ) {

            throw new Error(
                'Request timeout',
            );
        }

        throw error;

    } finally {

        timeoutController.clear();
    }
}

// =========================================
// GET
// =========================================

export function get(
    url,
    options = {},
)
{
    return request(
        url,
        {
            method:
                'GET',

            ...options,
        },
    );
}

// =========================================
// POST
// =========================================

export function post(
    url,
    body = {},
    options = {},
)
{
    return request(
        url,
        {
            method:
                'POST',

            responseType:
                'json',

            body:
                JSON.stringify(
                    body,
                ),

            headers:
            {
                'Content-Type':
                    'application/json',

                ...(options.headers || {}),
            },

            ...options,
        },
    );
}