import { showToast } from '../core/toast.js';

const paginationCache = new Map();

function isPaginationLink(element)
{
    return element?.closest('.collection-pagination-link') ?? null;
}

function getAjaxContainer()
{
    return document.querySelector('.collection-ajax-container');
}

function scrollToPaginationTop(container)
{
    container.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

function buildAjaxUrl(link)
{
    const url = new URL(link.href, window.location.origin);

    url.pathname = url.pathname.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );

    return url.toString();
}

function getCurrentPaginationAjaxUrl()
{
    const url = new URL(window.location.href);

    if (/\/manga\/collection$/.test(url.pathname))
    {
        url.pathname = url.pathname.replace(
            '/manga/collection',
            '/manga/collection-ajax/page/1'
        );

        return url.toString();
    }

    url.pathname = url.pathname.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );

    return url.toString();
}

function isCollectionPage()
{
    return (
        /\/manga\/collection$/.test(window.location.pathname)
        || /\/manga\/collection\/page\/\d+$/.test(window.location.pathname)
    );
}

async function preloadPage(url)
{
    if (!url || paginationCache.has(url))
    {
        return;
    }

    try
    {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok)
        {
            return;
        }

        const html = await response.text();

        paginationCache.set(url, html);
    }
    catch (error)
    {
        // preload silencieux
    }
}

function preloadNextPaginationLink(container)
{
    const activeLink = container.querySelector('.collection-pagination-link.active');

    if (!activeLink)
    {
        return;
    }

    const nextLink = activeLink.nextElementSibling;

    if (!nextLink?.classList.contains('collection-pagination-link'))
    {
        return;
    }

    preloadPage(buildAjaxUrl(nextLink));
}

async function fetchPaginationHtml(ajaxUrl)
{
    const cachedHtml = paginationCache.get(ajaxUrl);

    if (cachedHtml)
    {
        return cachedHtml;
    }

    const response = await fetch(ajaxUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    if (!response.ok)
    {
        throw new Error('Erreur AJAX pagination');
    }

    const html = await response.text();

    paginationCache.set(ajaxUrl, html);

    return html;
}

async function loadPaginationContent(
    ajaxUrl,
    container,
    fallbackUrl,
    errorMessage,
    shouldScroll = true
)
{
    try
    {
        container.classList.add('is-loading');

        const html = await fetchPaginationHtml(ajaxUrl);

        container.innerHTML = html;

        preloadNextPaginationLink(container);

        if (shouldScroll)
        {
            requestAnimationFrame(() =>
            {
                scrollToPaginationTop(container);
            });
        }

        return true;
    }
    catch (error)
    {
        showToast(errorMessage, 'error');
        window.location.href = fallbackUrl;

        return false;
    }
    finally
    {
        container.classList.remove('is-loading');
    }
}

export function initPaginationAjax()
{
    const container = getAjaxContainer();

    if (!container)
    {
        return;
    }

    document.addEventListener('pointerover', (event) =>
    {
        if (event.pointerType && event.pointerType !== 'mouse')
        {
            return;
        }

        const link = isPaginationLink(event.target);

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('focusin', (event) =>
    {
        const link = isPaginationLink(event.target);

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('click', async (event) =>
    {
        const link = isPaginationLink(event.target);

        if (!link)
        {
            return;
        }

        event.preventDefault();

        const ajaxUrl = buildAjaxUrl(link);

        const success = await loadPaginationContent(
            ajaxUrl,
            container,
            link.href,
            'Erreur chargement page',
            true
        );

        if (!success)
        {
            return;
        }

        history.pushState(
            {
                ajaxUrl: ajaxUrl,
                pageUrl: link.href
            },
            '',
            link.href
        );
    });

    window.addEventListener('popstate', async () =>
    {
        if (!isCollectionPage())
        {
            return;
        }

        const pageMatch = window.location.pathname.match(/\/manga\/collection\/page\/(\d+)$/);
        const page = pageMatch ? pageMatch[1] : '1';

        await loadPaginationContent(
            getCurrentPaginationAjaxUrl(),
            container,
            window.location.href,
            `Erreur chargement page ${page}`,
            true
        );
    });

    preloadNextPaginationLink(container);
}