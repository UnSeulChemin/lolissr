const collectionPagePrefetchCache = new Set();
const collectionImagePrefetchCache = new Set();

function canPrefetchCollectionUrl(url)
{
    return Boolean(url) && !collectionPagePrefetchCache.has(url);
}

function canPrefetchCollectionImage(url)
{
    return Boolean(url) && !collectionImagePrefetchCache.has(url);
}

export function prefetchCollectionPage(url)
{
    if (!canPrefetchCollectionUrl(url))
    {
        return;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
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
    });
}

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

function getCollectionCardLinkFromEventTarget(target)
{
    return target?.closest('.collection-card-link') ?? null;
}

export function initCollectionCardPrefetch()
{
    if (document.body.dataset.collectionCardPrefetchInit === 'true')
    {
        return;
    }

    document.body.dataset.collectionCardPrefetchInit = 'true';

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

        prefetchCollectionPage(cardLink.href);

        const image = cardLink.querySelector('.card-image-portrait');

        if (image)
        {
            prefetchCollectionImage(image.src);
        }
    });

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