const detailPageCache = new Set();
const imageCache = new Set();

export function preloadUrl(url)
{
    if (!url || detailPageCache.has(url))
    {
        return;
    }

    detailPageCache.add(url);

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).catch(() =>
    {
        // silence volontaire
    });
}

export function preloadImage(url)
{
    if (!url || imageCache.has(url))
    {
        return;
    }

    imageCache.add(url);

    const img = new Image();
    img.src = url;
}

export function initCardPrefetch()
{
    document.addEventListener('mouseover', (event) =>
    {
        const link = event.target.closest('.collection-card-link');

        if (!link)
        {
            return;
        }

        preloadUrl(link.href);

        const image = link.querySelector('.card-image');

        if (image)
        {
            preloadImage(image.src);
        }
    });

    document.addEventListener('focusin', (event) =>
    {
        const link = event.target.closest('.collection-card-link');

        if (!link)
        {
            return;
        }

        preloadUrl(link.href);

        const image = link.querySelector('.card-image');

        if (image)
        {
            preloadImage(image.src);
        }
    });
}