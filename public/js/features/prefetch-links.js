const preloadedLinks = new Set();
let hoverTimer = null;

function canPreloadLink(link)
{
    if (!link || !link.href)
    {
        return false;
    }

    if (
        link.classList.contains('collection-card-link') ||
        link.classList.contains('collection-pagination-link')
    )
    {
        return false;
    }

    if (link.target === '_blank' || link.hasAttribute('download'))
    {
        return false;
    }

    const href = link.getAttribute('href');

    if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:') ||
        href.startsWith('javascript:')
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

async function preloadLink(url)
{
    if (preloadedLinks.has(url))
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

        preloadedLinks.add(url);
    }
    catch (error)
    {
        // silencieux
    }
}

export function initLinkPreloading()
{
    if (navigator.connection?.saveData)
    {
        return;
    }

    document.addEventListener('pointerover', (event) =>
    {
        if (event.pointerType && event.pointerType !== 'mouse')
        {
            return;
        }

        const link = event.target.closest('a');

        if (!canPreloadLink(link))
        {
            return;
        }

        clearTimeout(hoverTimer);

        hoverTimer = setTimeout(() =>
        {
            preloadLink(link.href);
        }, 120);
    });

    document.addEventListener('pointerout', (event) =>
    {
        const link = event.target.closest('a');

        if (!link)
        {
            return;
        }

        clearTimeout(hoverTimer);
    });

    document.addEventListener('focusin', (event) =>
    {
        const link = event.target.closest('a');

        if (!canPreloadLink(link))
        {
            return;
        }

        preloadLink(link.href);
    });
}