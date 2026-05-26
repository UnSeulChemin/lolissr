// =========================================
// AJAX FETCH (CLEAN)
// =========================================

import { request } from '../core/http.js';
import { normalizeUrl } from '../core/navigation.js';
import { getPrefetchedPage } from './prefetch.js';
import { debug, debugError } from '../core/debug.js';

const SELECTOR = '.ajax-content';

function isValid(html)
{
    if (!html || typeof html !== 'string') return false;

    const doc = new DOMParser().parseFromString(html, 'text/html');

    return !!doc.querySelector(SELECTOR);
}

export async function fetchPageHtml(href, options = {})
{
    const url = normalizeUrl(href);

    const cached = getPrefetchedPage(url);

    if (typeof cached === 'string' && isValid(cached)) {
        debug('FETCH', 'cache-hit', url);
        return cached;
    }

    try {

        const html = await request(url, {
            responseType: 'text',
            signal: options.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });

        if (!isValid(html)) {
            throw new Error('Invalid AJAX HTML');
        }

        return html;

    } catch (e) {

        debugError('FETCH', e);
        throw e;
    }
}