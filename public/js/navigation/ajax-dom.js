// =========================================
// AJAX DOM
// =========================================

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const SELECTOR =
    '.ajax-content';

// =========================================
// PARSE
// =========================================

function parseHtml(html)
{
    const doc =
        new DOMParser()
            .parseFromString(
                html,
                'text/html',
            );

    if (
        !doc.querySelector(
            SELECTOR,
        )
    ) {

        throw new Error(
            'Missing AJAX container',
        );
    }

    return doc;
}

// =========================================
// META
// =========================================

function updateMeta(doc)
{
    const title =
        doc.querySelector(
            'title',
        );

    if (title) {
        document.title =
            title.textContent;
    }

    if (
        doc.documentElement.lang
    ) {

        document.documentElement.lang =
            doc.documentElement.lang;
    }
}

// =========================================
// SWAP
// =========================================

export function replaceContent(html)
{
    try {

        const doc =
            parseHtml(
                html,
            );

        const current =
            document.querySelector(
                SELECTOR,
            );

        const next =
            doc.querySelector(
                SELECTOR,
            );

        if (
            !current
            || !next
        ) {

            throw new Error(
                'Missing container',
            );
        }

        updateMeta(
            doc,
        );

        current.innerHTML =
            next.innerHTML;

        debug(
            'DOM',
            'swapped',
        );

    } catch (error) {

        debugError(
            'DOM',
            error,
        );
    }
}