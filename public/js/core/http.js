// =========================================
// CORE : HTTP
// =========================================

/**
 * CSRF TOKEN
 */
function getCsrfToken()
{
    return window.csrfToken || '';
}

/**
 * BUILD HEADERS
 */
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

/**
 * SAFE RESPONSE PARSER
 */
/**
 * SAFE RESPONSE PARSER
 */
async function parseResponse(
    res,
    type = 'text',
)
{
    // =============================
    // TEXT
    // =============================

    if (type === 'text') {

        return await res.text();
    }

    // =============================
    // JSON
    // =============================

    const text =
        await res.text();

    if (
        text.trim() === ''
    ) {
        return null;
    }

    try {

        return JSON.parse(
            text,
        );

    } catch (error) {

        console.error(
            'Invalid JSON response:',
            text,
        );

        throw new Error(
            'Invalid JSON response',
        );
    }
}

/**
 * MAIN REQUEST
 */
export async function request(
    url,
    options = {},
)
{
    const responseType =
        options.responseType
        || 'text';

    const res =
        await fetch(
            url,
            {
                credentials:
                    'same-origin',

                ...options,

                headers:
                    buildHeaders(
                        options.headers,
                    ),
            },
        );

    const data =
        await parseResponse(
            res,
            responseType,
        );

    // =============================
    // HTTP ERROR
    // =============================

    if (!res.ok) {

        throw {
            status:
                res.status,

            data,
        };
    }

    return data;
}

/**
 * GET
 */
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

/**
 * POST
 */
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