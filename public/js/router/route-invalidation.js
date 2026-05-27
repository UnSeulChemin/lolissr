// =========================================
// ROUTE INVALIDATION
// =========================================

const invalidatedRoutes =
    new Set();

// =========================================
// INVALIDATE
// =========================================

export function invalidateRoute(
    route,
)
{
    invalidatedRoutes.add(
        route,
    );
}

// =========================================
// CHECK
// =========================================

export function shouldRefreshRoute(
    route,
)
{
    return invalidatedRoutes.has(
        route,
    );
}

// =========================================
// CLEAR
// =========================================

export function clearInvalidatedRoute(
    route,
)
{
    invalidatedRoutes.delete(
        route,
    );
}