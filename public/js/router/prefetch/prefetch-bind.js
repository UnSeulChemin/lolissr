import { config } from '../../core/config.js';
import { shouldIgnoreLink } from '../../core/navigation.js';
import { NAVIGATION_START } from '../../core/navigation-protocol.js';
import { prefetchPage } from './prefetch-request.js';

const timers = new Map();
let initialized = false;
function cancel(link)
{
    clearTimeout(timers.get(link));
    timers.delete(link);
}
function cancelAll()
{
    for (const link of timers.keys()) cancel(link);
}
function eligible(link)
{
    return link instanceof HTMLAnchorElement && link.hasAttribute('data-prefetch')
        && !shouldIgnoreLink(link) && !link.hasAttribute('data-confirm-logout')
        && !link.pathname.endsWith('/deconnexion');
}
export function bindPrefetch()
{
    if (initialized) return;
    initialized = true;
    // Capture handles non-bubbling events without scanning page links.
    document.addEventListener('pointerenter', event =>
    {
        const link = event.target;
        if (!eligible(link)) return;
        cancel(link);
        timers.set(link, window.setTimeout(() =>
        {
            timers.delete(link);
            if (link.isConnected && eligible(link)) void prefetchPage(link.href);
        }, config.prefetch.hoverDelay));
    }, {capture: true, passive: true});
    document.addEventListener('pointerleave', event => cancel(event.target), {capture: true, passive: true});
    document.addEventListener(NAVIGATION_START, cancelAll);
    document.addEventListener('router:loaded', cancelAll);
    window.addEventListener('pagehide', cancelAll);
}
