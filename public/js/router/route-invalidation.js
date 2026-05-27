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
    const normalized =
        normalizeUrl(
            href,
        );

    invalidatedRoutes.add(
        normalized,
    );
}

// =========================================
// CHECK
// =========================================

export function shouldRefreshRoute(
    href,
)
{
    const normalized =
        normalizeUrl(
            href,
        );

    return invalidatedRoutes.has(
        normalized,
    );
}

// =========================================
// CLEAR
// =========================================

export function clearInvalidatedRoute(
    href,
)
{
    const normalized =
        normalizeUrl(
            href,
        );

    invalidatedRoutes.delete(
        normalized,
    );
}

// =========================================
// CLEAR ALL
// =========================================

export function clearAllInvalidatedRoutes()
{
    invalidatedRoutes.clear();
}