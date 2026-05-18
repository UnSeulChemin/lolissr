/*
|------------------------------------------------------------------
| Cache mémoire
|------------------------------------------------------------------
*/

const seriesPagePrefetchCache = new Set();

const seriesPagePrefetchPending = new Set();

const seriesImagePrefetchCache = new Set();

const seriesCardHoverTimers = new WeakMap();


/*
|------------------------------------------------------------------
| Vérifications
|------------------------------------------------------------------
*/

function canPrefetchSeriesUrl(url)
{
    return Boolean(url)
        && !seriesPagePrefetchCache.has(url)
        && !seriesPagePrefetchPending.has(url);
}

function canPrefetchSeriesImage(url)
{
    return Boolean(url)
        && !seriesImagePrefetchCache.has(url);
}


/*
|------------------------------------------------------------------
| Prefetch page manga
|------------------------------------------------------------------
*/

export function prefetchSeriesPage(url)
{
    if (!canPrefetchSeriesUrl(url))
    {
        return;
    }

    seriesPagePrefetchPending.add(url);

    fetch(url,
    {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then((response) =>
    {
        if (!response.ok)
        {
            return;
        }

        seriesPagePrefetchCache.add(url);
    })
    .catch(() =>
    {
        // silence volontaire
    })
    .finally(() =>
    {
        seriesPagePrefetchPending.delete(url);
    });
}


/*
|------------------------------------------------------------------
| Prefetch image
|------------------------------------------------------------------
*/

export function prefetchSeriesImage(url)
{
    if (!canPrefetchSeriesImage(url))
    {
        return;
    }

    seriesImagePrefetchCache.add(url);

    const image = new Image();

    image.src = url;
}


/*
|------------------------------------------------------------------
| Utilitaire
|------------------------------------------------------------------
*/

function getSeriesCardLinkFromEventTarget(target)
{
    return target?.closest(
        '.collection-card-link'
    ) ?? null;
}


/*
|------------------------------------------------------------------
| Hover
|------------------------------------------------------------------
*/

function scheduleSeriesCardPrefetch(cardLink)
{
    const existingTimer =
        seriesCardHoverTimers.get(cardLink);

    if (existingTimer)
    {
        clearTimeout(existingTimer);
    }

    const hoverTimer = setTimeout(() =>
    {
        prefetchSeriesPage(cardLink.href);

        const image = cardLink.querySelector(
            '.card-image-portrait'
        );

        if (image)
        {
            prefetchSeriesImage(image.src);
        }

        seriesCardHoverTimers.delete(cardLink);
    }, 120);

    seriesCardHoverTimers.set(
        cardLink,
        hoverTimer
    );
}

function cancelSeriesCardPrefetchHover(cardLink)
{
    const hoverTimer =
        seriesCardHoverTimers.get(cardLink);

    if (!hoverTimer)
    {
        return;
    }

    clearTimeout(hoverTimer);

    seriesCardHoverTimers.delete(cardLink);
}


/*
|------------------------------------------------------------------
| Initialisation globale
|------------------------------------------------------------------
*/

export function initPrefetchSeries()
{
    if (
        document.body.dataset
            .prefetchSeriesInit === 'true'
    )
    {
        return;
    }

    document.body.dataset
        .prefetchSeriesInit = 'true';

    /*
    |--------------------------------------------------------------
    | Hover souris
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'pointerover',
        (event) =>
        {
            if (
                event.pointerType
                && event.pointerType !== 'mouse'
            )
            {
                return;
            }

            const cardLink =
                getSeriesCardLinkFromEventTarget(
                    event.target
                );

            if (!cardLink)
            {
                return;
            }

            const previousCardLink =
                getSeriesCardLinkFromEventTarget(
                    event.relatedTarget
                );

            if (
                previousCardLink === cardLink
            )
            {
                return;
            }

            scheduleSeriesCardPrefetch(
                cardLink
            );
        }
    );

    document.addEventListener(
        'pointerout',
        (event) =>
        {
            const cardLink =
                getSeriesCardLinkFromEventTarget(
                    event.target
                );

            if (!cardLink)
            {
                return;
            }

            const nextCardLink =
                getSeriesCardLinkFromEventTarget(
                    event.relatedTarget
                );

            if (nextCardLink === cardLink)
            {
                return;
            }

            cancelSeriesCardPrefetchHover(
                cardLink
            );
        }
    );

    /*
    |--------------------------------------------------------------
    | Navigation clavier (focus)
    |--------------------------------------------------------------
    */

    document.addEventListener(
        'focusin',
        (event) =>
        {
            const cardLink =
                getSeriesCardLinkFromEventTarget(
                    event.target
                );

            if (!cardLink)
            {
                return;
            }

            prefetchSeriesPage(
                cardLink.href
            );

            const image =
                cardLink.querySelector(
                    '.card-image-portrait'
                );

            if (image)
            {
                prefetchSeriesImage(
                    image.src
                );
            }
        }
    );
}