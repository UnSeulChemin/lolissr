import { showToast } from '../core/toast.js';

const paginationCache = new Map();

function scrollToPaginationTop(container)
{
    container.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
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

function buildAjaxUrl(link)
{
    const url = new URL(link.href, window.location.origin);

    url.pathname = url.pathname.replace(
        '/manga/collection/page/',
        '/manga/collection-ajax/page/'
    );

    return url.toString();
}

function preloadNextPaginationLink(container)
{
    const activeLink = container.querySelector('.collection-pagination-link.active');

    if (!activeLink)
    {
        return;
    }

    const nextLink = activeLink.nextElementSibling;

    if (!nextLink)
    {
        return;
    }

    if (!nextLink.classList.contains('collection-pagination-link'))
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
    const container = document.querySelector('.collection-ajax-container');

    if (!container)
    {
        return;
    }

    document.addEventListener('mouseover', (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('focusin', (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

        if (!link)
        {
            return;
        }

        preloadPage(buildAjaxUrl(link));
    });

    document.addEventListener('click', async (event) =>
    {
        const link = event.target.closest('.collection-pagination-link');

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
        const match = window.location.pathname.match(/\/manga\/collection\/page\/(\d+)$/);

        if (!match)
        {
            return;
        }

        const page = match[1];
        const pageUrl = window.location.href;
        const ajaxUrl = pageUrl.replace(
            '/manga/collection/page/',
            '/manga/collection-ajax/page/'
        );

        await loadPaginationContent(
            ajaxUrl,
            container,
            pageUrl,
            `Erreur chargement page ${page}`,
            true
        );
    });

    preloadNextPaginationLink(container);
}