// =================================================
// ÉVÉNEMENTS DU ROUTEUR
// =================================================

// =================================================
// ROUTEUR CHARGÉ
// =================================================

export function dispatchRouterLoaded(target)
{
    document.dispatchEvent(
        new CustomEvent(
            'router:loaded',
            {
                detail: {
                    href: target
                }
            }
        )
    );
}