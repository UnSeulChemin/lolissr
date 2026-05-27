// =========================================
// ROUTE INVALIDATION
// =========================================

import {
    normalizeUrl,
} from '../core/navigation.js';

// =========================================
// STATE
// =========================================

const invalidatedRoutes =
    new Set();

// =========================================
// INVALIDATE
// =========================================

export function invalidateRoute(
    href,
)
{
    invalidatedRoutes.add(
        normalizeUrl(
            href,
        ),
    );
}

// =========================================
// CHECK
// =========================================

export function shouldRefreshRoute(
    href,
)
{
    return invalidatedRoutes.has(
        normalizeUrl(
            href,
        ),
    );
}

// =========================================
// CLEAR
// =========================================

export function clearInvalidatedRoute(
    href,
)
{
    invalidatedRoutes.delete(
        normalizeUrl(
            href,
        ),
    );
}