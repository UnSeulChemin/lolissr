// =========================================
// AJAX DOM
// =========================================

import {
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const CONTENT_SELECTOR =
    '.ajax-content';

// =========================================
// PARSE HTML
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
            CONTENT_SELECTOR,
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
// UPDATE META
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
// SYNC BODY ATTRIBUTES
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

    const preservedDataset =
    {
        appInitialized:
            document.body.dataset
                .appInitialized,
    };

    // =====================================
    // REMOVE OLD DATA ATTRIBUTES
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
    // APPLY NEW DATA ATTRIBUTES
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

                    const value =
                        nextBody.getAttribute(
                            attribute,
                        );

                    if (
                        value !== null
                    ) {

                        document.body.setAttribute(
                            attribute,
                            value,
                        );
                    }
                }
            },
        );

    // =====================================
    // RESTORE SPA FLAGS
    // =====================================

    if (
        preservedDataset.appInitialized
    ) {

        document.body.dataset
            .appInitialized =
                preservedDataset.appInitialized;
    }
}

// =========================================
// REPLACE CONTENT
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

        const currentContainer =
            document.querySelector(
                CONTENT_SELECTOR,
            );

        if (
            !currentContainer
        ) {

            throw new Error(
                'Missing current AJAX container',
            );
        }

        updateMeta(
            doc,
        );

        syncBodyAttributes(
            doc,
        );

        currentContainer.innerHTML =
            container.innerHTML;

    } catch (error) {

        debugError(
            'AJAX-DOM',
            error,
        );

        throw error;
    }
}