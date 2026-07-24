// =========================================
// ROUTER EVENTS
// =========================================

// =========================================
// ROUTER LOADED
// =========================================

export function dispatchRouterLoaded(
    target,
)
{
    document.dispatchEvent(
        new CustomEvent(
            'router:loaded',
            {
                detail:
                {
                    href:
                        target,
                },
            },
        ),
    );
}