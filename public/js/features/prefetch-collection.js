/*
|------------------------------------------------------------------
| Cache mémoire
|------------------------------------------------------------------
*/

const collectionPagePrefetchCache = new Set();
const collectionPagePrefetchPending = new Set();
const collectionImagePrefetchCache = new Set();
const collectionCardHoverTimers = new WeakMap();


/*
|------------------------------------------------------------------
| Vérifications
|------------------------------------------------------------------
*/

function canPrefetchCollectionUrl(url)
{
    return Boolean(url)
        && !collectionPagePrefetchCache.has(url)
        && !collectionPagePrefetchPending.has(url);
}

function canPrefetchCollectionImage(url)
{
    return Boolean(url)
        && !collectionImagePrefetchCache.has(url);
}


/*
|------------------------------------------------------------------
| Prefetch page manga
|------------------------------------------------------------------
*/

export function prefetchCollectionPage(url)
{
    if (!canPrefetchCollectionUrl(url))
    {
        return;
    }

    collectionPagePrefetchPending.add(url);

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then((response) =>
    {
        if (!response.ok)
        {
            return;
        }

        collectionPagePrefetchCache.add(url);
    })
    .catch(() =>
    {
        // silence volontaire
    })
    .finally(() =>
    {
        collectionPagePrefetchPending.delete(url);
    });
}


/*
|------------------------------------------------------------------
| Prefetch image
|------------------------------------------------------------------
*/

export function prefetchCollectionImage(url)
{
    if (!canPrefetchCollectionImage(url))
    {
        return;
    }

    collectionImagePrefetchCache.add(url);

    const image = new Image();
    image.src = url;
}


/*
|------------------------------------------------------------------
| Utilitaire
|------------------------------------------------------------------
*/

function getCollectionCardLinkFromEventTarget(target)
{
    return target?.closest('.collection-card-link') ?? null;
}


/*
|------------------------------------------------------------------
| Hover
|------------------------------------------------------------------
*/

function scheduleCollectionCardPrefetch(cardLink)
{
    const existingTimer = collectionCardHoverTimers.get(cardLink);

    if (existingTimer)
    {
        clearTimeout(existingTimer);
    }

    const hoverTimer = setTimeout(() =>
    {
        prefetchCollectionPage(cardLink.href);

        const image = cardLink.querySelector('.card-image-portrait');

        if (image)
        {
            prefetchCollectionImage(image.src);
        }

        collectionCardHoverTimers.delete(cardLink);
    }, 120);

    collectionCardHoverTimers.set(cardLink, hoverTimer);
}

function cancelCollectionCardPrefetchHover(cardLink)
{
    const hoverTimer = collectionCardHoverTimers.get(cardLink);

    if (!hoverTimer)
    {
        return;
    }

    clearTimeout(hoverTimer);
    collectionCardHoverTimers.delete(cardLink);
}


/*
|------------------------------------------------------------------
| Initialisation globale
|------------------------------------------------------------------
*/

export function initCollectionCardPrefetch()
{
    if (document.body.dataset.collectionCardPrefetchInit === 'true')
    {
        return;
    }

    document.body.dataset.collectionCardPrefetchInit = 'true';

    /*
    |--------------------------------------------------------------
    | Hover souris
    |--------------------------------------------------------------
    */

    document.addEventListener('pointerover', (event) =>
    {
        if (event.pointerType && event.pointerType !== 'mouse')
        {
            return;
        }

        const cardLink = getCollectionCardLinkFromEventTarget(event.target);

        if (!cardLink)
        {
            return;
        }

        const previousCardLink = getCollectionCardLinkFromEventTarget(
            event.relatedTarget
        );

        if (previousCardLink === cardLink)
        {
            return;
        }

        scheduleCollectionCardPrefetch(cardLink);
    });

    document.addEventListener('pointerout', (event) =>
    {
        const cardLink = getCollectionCardLinkFromEventTarget(event.target);

        if (!cardLink)
        {
            return;
        }

        const nextCardLink = getCollectionCardLinkFromEventTarget(
            event.relatedTarget
        );

        if (nextCardLink === cardLink)
        {
            return;
        }

        cancelCollectionCardPrefetchHover(cardLink);
    });

    /*
    |--------------------------------------------------------------
    | Navigation clavier (focus)
    |--------------------------------------------------------------
    */

    document.addEventListener('focusin', (event) =>
    {
        const cardLink = getCollectionCardLinkFromEventTarget(event.target);

        if (!cardLink)
        {
            return;
        }

        prefetchCollectionPage(cardLink.href);

        const image = cardLink.querySelector('.card-image-portrait');

        if (image)
        {
            prefetchCollectionImage(image.src);
        }
    });
}