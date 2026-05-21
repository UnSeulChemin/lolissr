/*
|------------------------------------------------------------------
| Cache mémoire
|------------------------------------------------------------------
*/

const linkPrefetchCache = new Set();

const linkPrefetchPending = new Set();

const linkHoverTimers = new WeakMap();


/*
|------------------------------------------------------------------
| Vérifications
|------------------------------------------------------------------
*/

function canPrefetchLink(link)
{
    if (!link?.href)
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Liens AJAX déjà gérés ailleurs
    |------------------------------------------------------------------
    */

    if (
        link.classList.contains(
            'collection-card-link'
        )
        || link.classList.contains(
            'collection-pagination-link'
        )
    )
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Liens exclus
    |------------------------------------------------------------------
    */

    if (
        link.target === '_blank'
        || link.hasAttribute('download')
    )
    {
        return false;
    }

    const href =
        link.getAttribute('href');

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

    const url = new URL(
        link.href,
        window.location.origin
    );

    /*
    |------------------------------------------------------------------
    | Même domaine uniquement
    |------------------------------------------------------------------
    */

    if (
        url.origin
        !== window.location.origin
    )
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Ignore routes AJAX
    |------------------------------------------------------------------
    */

    if (
        url.pathname.includes('/ajax/')
    )
    {
        return false;
    }

    /*
    |------------------------------------------------------------------
    | Ignore page manga index
    |------------------------------------------------------------------
    */

    if (
        url.pathname.endsWith('/manga')
    )
    {
        return false;
    }

    const absoluteUrl =
        url.toString();

    /*
    |------------------------------------------------------------------
    | Déjà chargé / déjà en cours
    |------------------------------------------------------------------
    */

    if (
        linkPrefetchCache.has(
            absoluteUrl
        )
        || linkPrefetchPending.has(
            absoluteUrl
        )
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

async function prefetchLink(url)
{
    if (!url)
    {
        return;
    }

    if (
        linkPrefetchCache.has(url)
        || linkPrefetchPending.has(url)
    )
    {
        return;
    }

    linkPrefetchPending.add(url);

    try
    {
        const response = await fetch(
            url,
            {
                method: 'GET',
                credentials:
                    'same-origin'
            }
        );

        if (!response.ok)
        {
            return;
        }

        linkPrefetchCache.add(url);
    }
    catch
    {
        /*
        |--------------------------------------------------------------
        | Prefetch silencieux
        |--------------------------------------------------------------
        */
    }
    finally
    {
        linkPrefetchPending.delete(url);
    }
}


/*
|------------------------------------------------------------------
| Hover par lien
|------------------------------------------------------------------
*/

function scheduleLinkPrefetch(link)
{
    const existingTimer =
        linkHoverTimers.get(link);

    if (existingTimer)
    {
        clearTimeout(existingTimer);
    }

    const hoverTimer = setTimeout(() =>
    {
        prefetchLink(link.href);

        linkHoverTimers.delete(link);
    }, 120);

    linkHoverTimers.set(
        link,
        hoverTimer
    );
}

function cancelLinkPrefetch(link)
{
    const hoverTimer =
        linkHoverTimers.get(link);

    if (!hoverTimer)
    {
        return;
    }

    clearTimeout(hoverTimer);

    linkHoverTimers.delete(link);
}


/*
|------------------------------------------------------------------
| Initialisation globale
|------------------------------------------------------------------
*/

export function initPrefetchLinks()
{
    /*
    |------------------------------------------------------------------
    | Économie de données
    |------------------------------------------------------------------
    */

    if (navigator.connection?.saveData)
    {
        return;
    }

    /*
    |------------------------------------------------------------------
    | Sécurité double init
    |------------------------------------------------------------------
    */

    if (
        document.body.dataset
            .prefetchLinksInit === 'true'
    )
    {
        return;
    }

    document.body.dataset
        .prefetchLinksInit = 'true';

    /*
    |------------------------------------------------------------------
    | Hover souris
    |------------------------------------------------------------------
    */

    document.addEventListener(
        'pointerover',
        (event) =>
        {
            if (
                event.pointerType
                && event.pointerType
                    !== 'mouse'
            )
            {
                return;
            }

            const link =
                event.target.closest('a');

            if (!canPrefetchLink(link))
            {
                return;
            }

            /*
            |--------------------------------------------------------------
            | Ignore mouvements internes
            |--------------------------------------------------------------
            */

            const previousLink =
                event.relatedTarget
                    ?.closest?.('a');

            if (previousLink === link)
            {
                return;
            }

            scheduleLinkPrefetch(link);
        }
    );

    document.addEventListener(
        'pointerout',
        (event) =>
        {
            const link =
                event.target.closest('a');

            if (!link)
            {
                return;
            }

            /*
            |--------------------------------------------------------------
            | Ignore mouvements internes
            |--------------------------------------------------------------
            */

            const nextLink =
                event.relatedTarget
                    ?.closest?.('a');

            if (nextLink === link)
            {
                return;
            }

            cancelLinkPrefetch(link);
        }
    );

    /*
    |------------------------------------------------------------------
    | Focus clavier
    |------------------------------------------------------------------
    */

    document.addEventListener(
        'focusin',
        (event) =>
        {
            const link =
                event.target.closest('a');

            if (!canPrefetchLink(link))
            {
                return;
            }

            prefetchLink(link.href);
        }
    );
}