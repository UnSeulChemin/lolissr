const preloadedLinks = new Set();
let hoverTimer = null;

function canPreloadLink(link)
{
    if (!link)
    {
        return false;
    }

    if (!link.href)
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

    preloadedLinks.add(url);

    try
    {
        await fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        });
    }
    catch (error)
    {
        // silencieux
    }
}

export function initLinkPreloading()
{
    document.addEventListener('mouseover', (event) =>
    {
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