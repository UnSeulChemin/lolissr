// =========================================
// PAGE INVALIDATION
// =========================================

import {
    invalidateRoute,
} from './route-invalidation.js';

import {
    invalidatePrefetch,
} from './prefetch/prefetch-cache.js';

// =========================================
// INVALIDATE PAGE
// =========================================

export function invalidatePage(
    href,
    options = {},
)
{
    invalidateRoute(
        href,
        options,
    );

    invalidatePrefetch(
        href,
        options,
    );
}
