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
            title.textContent;
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

export function replaceContent(
    html,
)
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

        // =================================
        // META
        // =================================

        updateMeta(
            doc,
        );

        // =================================
        // BODY DATASET
        // =================================

        syncBodyAttributes(
            doc,
        );

        // =================================
        // SWAP
        // =================================

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