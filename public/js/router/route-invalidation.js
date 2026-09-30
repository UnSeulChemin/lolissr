// =========================================
// ROUTE INVALIDATION
// =========================================

import {
    normalizeCacheKey,
} from '../core/navigation.js';

// =========================================
// STATE
// =========================================

const invalidatedRoutes = new Map();

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
    descendants,
)
{
    return current === invalidated
        || (descendants && current.startsWith(invalidated === '/' ? '/' : `${invalidated}/`));
}

// =========================================
// INVALIDATE
// =========================================

export function invalidateRoute(href, {descendants = true} = {})
{
    invalidatedRoutes.set(
        routePath(href),
        descendants || invalidatedRoutes.get(routePath(href)) === true,
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

    for (const [route, descendants] of invalidatedRoutes)
    {
        if (matchesInvalidatedRoute(normalized, route, descendants))
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

    for (const [route, descendants] of invalidatedRoutes)
    {
        if (matchesInvalidatedRoute(normalized, route, descendants))
        {
            invalidatedRoutes.delete(
                route,
            );
        }
    }
}
