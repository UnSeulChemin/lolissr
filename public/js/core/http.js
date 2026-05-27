// =========================================
// CORE : HTTP
// =========================================

// =========================================
// CSRF
// =========================================

function getCsrfToken()
{
    return window.csrfToken || '';
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
// RESPONSE PARSER
// =========================================

async function parseResponse(
    response,
    type = 'text',
)
{
    // =====================================
    // TEXT
    // =====================================

    if (type === 'text') {

        return response.text();
    }

    // =====================================
    // JSON
    // =====================================

    const text =
        await response.text();

    if (
        text.trim() === ''
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

    const response =
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
            response,
            responseType,
        );

    // =====================================
    // HTTP ERROR
    // =====================================

    if (!response.ok) {

        const error =
            new Error(
                data?.message
                || `HTTP ${response.status}`,
            );

        error.status =
            response.status;

        error.data =
            data;

        throw error;
    }

    return data;
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