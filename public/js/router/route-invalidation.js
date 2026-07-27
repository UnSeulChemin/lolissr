// =========================================
// ROUTE INVALIDATION
// =========================================

import {
    normalizeCacheKey,
} from '../core/navigation.js';

// =========================================
// STATE
// =========================================

const invalidatedRoutes = new Set();

// =========================================
// HELPERS
// =========================================

function routePath(href)
{
    return new URL(
        normalizeCacheKey(href),
    ).pathname.replace(/\/+$/, '') || '/';
}

function matchesInvalidatedRoute(
    current,
    invalidated,
)
{
    return current === invalidated
        || current.startsWith(`${invalidated}/`);
}

// =========================================
// INVALIDATE
// =========================================

export function invalidateRoute(href)
{
    invalidatedRoutes.add(
        routePath(href),
    );
}

// =========================================
// CHECK
// =========================================

export function shouldRefreshRoute(href)
{
    const normalized = routePath(
        href,
    );

    for (const route of invalidatedRoutes)
    {
        if (matchesInvalidatedRoute(normalized, route))
        {
            return true;
        }
    }

    return false;
}

// =========================================
// CLEAR
// =========================================

export function clearInvalidatedRoute(href)
{
    const normalized = routePath(
        href,
    );

    for (const route of invalidatedRoutes)
    {
        if (matchesInvalidatedRoute(normalized, route))
        {
            invalidatedRoutes.delete(
                route,
            );
        }
    }
}

// =========================================
// CLEAR ALL
// =========================================

export function clearAllInvalidatedRoutes()
{
    invalidatedRoutes.clear();
}