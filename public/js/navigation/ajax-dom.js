// =========================================
// AJAX DOM (STABLE SPA SWAP)
// =========================================

import { $ } from '../core/dom.js';
import { debug, debugError } from '../core/debug.js';

const SELECTOR = '.ajax-content';

function parseHtml(html)
{
    const doc = new DOMParser().parseFromString(html, 'text/html');

    if (!doc.querySelector(SELECTOR)) {
        throw new Error('Invalid SPA HTML (missing container)');
    }

    return doc;
}

function getContainer(root)
{
    return root.querySelector(SELECTOR);
}

function updateMeta(doc)
{
    const title = doc.querySelector('title');
    if (title) document.title = title.textContent;

    const html = doc.documentElement;
    if (html?.lang) document.documentElement.lang = html.lang;
}

export function replaceContent(html)
{
    try {
        const doc = parseHtml(html);

        const current = document.querySelector(SELECTOR);
        const next = getContainer(doc);

        if (!current || !next) {
            throw new Error('Missing SPA container');
        }

        updateMeta(doc);

        // ⚡ IMPORTANT FIX: replace INNER ONLY (SAFE HEADER PERSIST)
        current.innerHTML = next.innerHTML;

        debug('DOM', 'swapped');

    } catch (e) {
        debugError('DOM', e);
    }
}