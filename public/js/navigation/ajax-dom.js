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

function parseHtml(
    html,
)
{
    const doc =
        new DOMParser()
            .parseFromString(
                html,
                'text/html',
            );

    const container =
        doc.querySelector(
            SELECTOR,
        );

    if (!container) {

        throw new Error(
            'Missing AJAX container',
        );
    }

    return doc;
}

// =========================================
// META
// =========================================

function updateMeta(
    doc,
)
{
    const title =
        doc.querySelector(
            'title',
        );

    if (title) {

        document.title =
            title.textContent?.trim()
            || document.title;
    }

    const lang =
        doc.documentElement.lang;

    if (lang) {

        document.documentElement.lang =
            lang;
    }
}

// =========================================
// BODY ATTRIBUTES
// =========================================

function syncBodyAttributes(
    doc,
)
{
    const nextBody =
        doc.body;

    if (!nextBody) {
        return;
    }

    // =====================================
    // KEEP SPA FLAGS
    // =====================================

    const currentFlags =
    {
        appInitialized:
            document.body.dataset
                .appInitialized,

        ajaxNavigating:
            document.body.dataset
                .ajaxNavigating,
    };

    // =====================================
    // RESET DATASET
    // =====================================

    document.body
        .getAttributeNames()
        .forEach(
            (
                attribute,
            ) =>
            {
                if (
                    attribute.startsWith(
                        'data-',
                    )
                ) {

                    document.body.removeAttribute(
                        attribute,
                    );
                }
            },
        );

    // =====================================
    // APPLY NEW DATASET
    // =====================================

    nextBody
        .getAttributeNames()
        .forEach(
            (
                attribute,
            ) =>
            {
                if (
                    attribute.startsWith(
                        'data-',
                    )
                ) {

                    document.body.setAttribute(
                        attribute,
                        nextBody.getAttribute(
                            attribute,
                        ),
                    );
                }
            },
        );

    // =====================================
    // RESTORE SPA FLAGS
    // =====================================

    if (
        currentFlags.appInitialized
    ) {

        document.body.dataset
            .appInitialized =
                currentFlags.appInitialized;
    }

    if (
        currentFlags.ajaxNavigating
    ) {

        document.body.dataset
            .ajaxNavigating =
                currentFlags.ajaxNavigating;
    }
}

// =========================================
// SWAP
// =========================================

// =========================================
// AJAX DOM
// =========================================

export function replaceContent(
    html,
)
{
    const parser =
        new DOMParser();

    const documentHtml =
        parser.parseFromString(
            html,
            'text/html',
        );

    // =====================================
    // NEW CONTENT
    // =====================================

    const newContent =
        documentHtml.querySelector(
            '.ajax-content',
        );

    const currentContent =
        document.querySelector(
            '.ajax-content',
        );

    if (
        !newContent
        || !currentContent
    ) {

        throw new Error(
            'Missing ajax content',
        );
    }

    // =====================================
    // REPLACE ONLY CONTENT
    // =====================================

    currentContent.innerHTML =
        newContent.innerHTML;

    // =====================================
    // TITLE
    // =====================================

    document.title =
        documentHtml.title;
}