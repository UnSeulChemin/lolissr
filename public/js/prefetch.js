const detailPageCache = new Set();

function preloadUrl(url)
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

function preloadImage(url)
{
    if (!url)
    {
        return;
    }

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