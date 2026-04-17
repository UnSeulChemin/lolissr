/*
|------------------------------------------------------------------
| Cache mémoire
|------------------------------------------------------------------
*/

const genericLinkPrefetchCache = new Set();
const genericLinkPrefetchPending = new Set();
const genericLinkHoverTimers = new WeakMap();


/*
|------------------------------------------------------------------
| Vérifications
|------------------------------------------------------------------
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
    const absoluteUrl = url.toString();

    if (url.origin !== window.location.origin)
    {
        return false;
    }

    if (
        genericLinkPrefetchCache.has(absoluteUrl)
        || genericLinkPrefetchPending.has(absoluteUrl)
    )
    {
        return false;
    }

    return true;
}


/*
|------------------------------------------------------------------
| Prefetch page HTML
|------------------------------------------------------------------
*/

async function prefetchGenericLink(url)
{
    if (!url)
    {
        return;
    }

    if (
        genericLinkPrefetchCache.has(url)
        || genericLinkPrefetchPending.has(url)
    )
    {
        return;
    }

    genericLinkPrefetchPending.add(url);

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
    finally
    {
        genericLinkPrefetchPending.delete(url);
    }
}


/*
|------------------------------------------------------------------
| Hover par lien
|------------------------------------------------------------------
*/

function scheduleGenericLinkPrefetch(link)
{
    const existingTimer = genericLinkHoverTimers.get(link);

    if (existingTimer)
    {
        clearTimeout(existingTimer);
    }

    const hoverTimer = setTimeout(() =>
    {
        prefetchGenericLink(link.href);
        genericLinkHoverTimers.delete(link);
    }, 120);

    genericLinkHoverTimers.set(link, hoverTimer);
}

function cancelGenericLinkPrefetch(link)
{
    const hoverTimer = genericLinkHoverTimers.get(link);

    if (!hoverTimer)
    {
        return;
    }

    clearTimeout(hoverTimer);
    genericLinkHoverTimers.delete(link);
}


/*
|------------------------------------------------------------------
| Initialisation globale
|------------------------------------------------------------------
*/

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

        /*
        |--------------------------------------------------------------
        | Ignore les mouvements internes au même lien
        |--------------------------------------------------------------
        */

        const previousLink = event.relatedTarget?.closest?.('a');

        if (previousLink === link)
        {
            return;
        }

        scheduleGenericLinkPrefetch(link);
    });

    document.addEventListener('pointerout', (event) =>
    {
        const link = event.target.closest('a');

        if (!link)
        {
            return;
        }

        /*
        |--------------------------------------------------------------
        | N'annule que si on quitte réellement le lien
        |--------------------------------------------------------------
        */

        const nextLink = event.relatedTarget?.closest?.('a');

        if (nextLink === link)
        {
            return;
        }

        cancelGenericLinkPrefetch(link);
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