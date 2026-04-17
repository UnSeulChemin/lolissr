const genericLinkPrefetchCache = new Set();
let genericLinkHoverTimer = null;

/**
 * Vérifie si un lien peut être préchargé.
 */
function canPrefetchGenericLink(link)
{
    if (!link || !link.href)
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Liens déjà gérés ailleurs
    |------------------------------------------------------------------
    */

    if (
        link.classList.contains('collection-card-link')
        || link.classList.contains('collection-pagination-link')
    )
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Liens exclus
    |------------------------------------------------------------------
    */

    if (link.target === '_blank' || link.hasAttribute('download'))
    {
        return false;
    }

    const href = link.getAttribute('href');

    if (
        !href
        || href.startsWith('#')
        || href.startsWith('mailto:')
        || href.startsWith('tel:')
        || href.startsWith('javascript:')
    )
    {
        return false;
    }

    const url = new URL(link.href, window.location.origin);

    if (url.origin !== window.location.origin)
    {
        return false;
    }

    return true;
}

/**
 * Précharge une page HTML classique.
 */
async function prefetchGenericLink(url)
{
    if (!url || genericLinkPrefetchCache.has(url))
    {
        return;
    }

    try
    {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        });

        if (!response.ok)
        {
            return;
        }

        genericLinkPrefetchCache.add(url);
    }
    catch (error)
    {
        // silencieux
    }
}

export function initLinkPreloading()
{
    /*
    |------------------------------------------------------------------
    | Sécurité
    |------------------------------------------------------------------
    */

    if (navigator.connection?.saveData)
    {
        return;
    }

    if (document.body.dataset.genericLinkPrefetchInit === 'true')
    {
        return;
    }

    document.body.dataset.genericLinkPrefetchInit = 'true';

    /*
    |------------------------------------------------------------------
    | Hover souris
    |------------------------------------------------------------------
    */

    document.addEventListener('pointerover', (event) =>
    {
        if (event.pointerType && event.pointerType !== 'mouse')
        {
            return;
        }

        const link = event.target.closest('a');

        if (!canPrefetchGenericLink(link))
        {
            return;
        }

        clearTimeout(genericLinkHoverTimer);

        genericLinkHoverTimer = setTimeout(() =>
        {
            prefetchGenericLink(link.href);
        }, 120);
    });

    document.addEventListener('pointerout', (event) =>
    {
        const link = event.target.closest('a');

        if (!link)
        {
            return;
        }

        clearTimeout(genericLinkHoverTimer);
    });

    /*
    |------------------------------------------------------------------
    | Focus clavier
    |------------------------------------------------------------------
    */

    document.addEventListener('focusin', (event) =>
    {
        const link = event.target.closest('a');

        if (!canPrefetchGenericLink(link))
        {
            return;
        }

        prefetchGenericLink(link.href);
    });
}