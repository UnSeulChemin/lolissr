// =========================================
// AJAX DOM
// =========================================

import {
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

    return {
        doc,
        container,
    };
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

    const currentFlags =
    {
        appInitialized:
            document.body.dataset
                .appInitialized,
    };

    // =====================================
    // CLEAR DATA ATTRIBUTES
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
    // APPLY NEW ATTRIBUTES
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
}

// =========================================
// SWAP
// =========================================

export function replaceContent(
    html,
)
{
    try {

        const {
            doc,
            container,
        } =
            parseHtml(
                html,
            );

        const currentContent =
            document.querySelector(
                SELECTOR,
            );

        if (
            !currentContent
        ) {

            throw new Error(
                'Missing current container',
            );
        }

        // =================================
        // META
        // =================================

        updateMeta(
            doc,
        );

        // =================================
        // BODY
        // =================================

        syncBodyAttributes(
            doc,
        );

        // =================================
        // CONTENT
        // =================================

        currentContent.innerHTML =
            container.innerHTML;

    } catch (error) {

        debugError(
            'AJAX-DOM',
            error,
        );

        throw error;
    }
}