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
        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        'X-Ajax':
            'true',

        'X-Requested-With':
            'XMLHttpRequest',

        /*
        |--------------------------------------------------------------------------
        | CSRF
        |--------------------------------------------------------------------------
        */

        'X-CSRF-TOKEN':
            getCsrfToken(),

        /*
        |--------------------------------------------------------------------------
        | ACCEPT
        |--------------------------------------------------------------------------
        */

        'Accept':
            'application/json',

        /*
        |--------------------------------------------------------------------------
        | CUSTOM
        |--------------------------------------------------------------------------
        */

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
                controller.abort();
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
// RESPONSE TYPE
// =========================================

function isJsonResponse(
    response,
)
{
    const contentType =
        response.headers.get(
            'content-type',
        ) || '';

    return contentType.includes(
        'application/json',
    );
}

// =========================================
// PARSE RESPONSE
// =========================================

async function parseResponse(
    response,
)
{
    /*
    |--------------------------------------------------------------------------
    | EMPTY RESPONSE
    |--------------------------------------------------------------------------
    */

    if (
        response.status === 204
    ) {

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON
    |--------------------------------------------------------------------------
    */

    if (
        isJsonResponse(
            response,
        )
    ) {

        try {

            return await response.json();

        } catch {

            throw new Error(
                'Invalid JSON response',
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TEXT
    |--------------------------------------------------------------------------
    */

    return response.text();
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

    error.response =
        response;

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
                timeoutController
                    .controller
                    .signal,
            ])
            : timeoutController
                .controller
                .signal;

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
            );

        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if (
            !response.ok
        ) {

            throw createHttpError(
                response,
                data,
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT RESPONSE
        |--------------------------------------------------------------------------
        */

        if (
            data?.type
            === 'redirect'
        ) {

            return {
                ...data,

                redirected:
                    true,
            };
        }

        return data;

    } catch (error) {

        debugError(
            'HTTP',
            error,
        );

        /*
        |--------------------------------------------------------------------------
        | TIMEOUT
        |--------------------------------------------------------------------------
        */

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

// =========================================
// PUT
// =========================================

export function put(
    url,
    body = {},
    options = {},
)
{
    return request(
        url,
        {
            method:
                'PUT',

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

// =========================================
// DELETE
// =========================================

export function destroy(
    url,
    options = {},
)
{
    return request(
        url,
        {
            method:
                'DELETE',

            ...options,
        },
    );
}