// =========================================
// PREFETCH CACHE
// =========================================

import {
    normalizeUrl,
} from '../../core/navigation.js';

import {
    debug,
} from '../../core/debug/debug.js';

import {
    cache,
    inFlight,
    invalidated,
} from './prefetch-state.js';

// =========================================
// CONFIG
// =========================================

const CACHE_DURATION =
    60000;

const MAX_CACHE_SIZE =
    50;

// =========================================
// HELPERS
// =========================================

function isExpired(
    entry,
)
{
    return (
        Date.now()
        - entry.timestamp
        > CACHE_DURATION
    );
}

function trimCache()
{
    while (
        cache.size
        > MAX_CACHE_SIZE
    )
    {
        const oldestKey =
            cache.keys()
                .next()
                .value;

        if (!oldestKey) {

            return;
        }

        cache.delete(
            oldestKey,
        );
    }
}

// =========================================
// CACHE
// =========================================

export function getPrefetchedPage(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    if (
        invalidated.has(
            url,
        )
    ) {

        return null;
    }

    const cached =
        cache.get(
            url,
        );

    if (!cached) {

        return null;
    }

    if (
        isExpired(
            cached,
        )
    ) {

        cache.delete(
            url,
        );

        return null;
    }

    return {
        type:
            'page',

        page:
            cached.page,
    };
}

export function setPrefetchedPage(
    url,
    response,
)
{
    trimCache();

    cache.set(
        url,
        {
            page:
                response.page,

            timestamp:
                Date.now(),
        },
    );

    invalidated.delete(
        url,
    );
}

// =========================================
// INVALIDATE
// =========================================

export function invalidatePrefetch(
    href,
)
{
    const url =
        normalizeUrl(
            href,
        );

    invalidated.add(
        url,
    );

    cache.delete(
        url,
    );

    inFlight.delete(
        url,
    );

    debug(
        'PREFETCH',
        'invalidate',
        url,
    );
}

// =========================================
// CLEAR
// =========================================

export function clearPrefetchCache()
{
    cache.clear();

    inFlight.clear();

    invalidated.clear();
}