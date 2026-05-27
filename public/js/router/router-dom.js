// =========================================
// ROUTER DOM
// =========================================

import {
    debugError,
} from '../core/debug.js';

// =========================================
// CONFIG
// =========================================

const CONTENT_SELECTOR =
    '.app-content';

// =========================================
// PARSE HTML
// =========================================

function parseHtml(
    html,
)
{
    const documentHtml =
        new DOMParser()
            .parseFromString(
                html,
                'text/html',
            );

    const nextContent =
        documentHtml.querySelector(
            CONTENT_SELECTOR,
        );

    if (!nextContent) {

        throw new Error(
            'Missing app content',
        );
    }

    return {
        documentHtml,
        nextContent,
    };
}

// =========================================
// UPDATE DOCUMENT
// =========================================

function updateDocumentMeta(
    documentHtml,
)
{
    // =====================================
    // TITLE
    // =====================================

    const title =
        documentHtml.querySelector(
            'title',
        );

    if (
        title?.textContent
    ) {

        document.title =
            title.textContent.trim();
    }

    // =====================================
    // LANG
    // =====================================

    const lang =
        documentHtml.documentElement.lang;

    if (lang) {

        document.documentElement.lang =
            lang;
    }
}

// =========================================
// SYNC BODY
// =========================================

function syncBodyAttributes(
    documentHtml,
)
{
    const nextBody =
        documentHtml.body;

    if (!nextBody) {
        return;
    }

    // =====================================
    // KEEP INTERNAL FLAGS
    // =====================================

    const preserved =
    {
        appInitialized:
            document.body.dataset
                .appInitialized,
    };

    // =====================================
    // REMOVE OLD DATA ATTRIBUTES
    // =====================================

    for (
        const attribute
        of document.body.getAttributeNames()
    )
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
    }

    // =====================================
    // APPLY NEW DATA ATTRIBUTES
    // =====================================

    for (
        const attribute
        of nextBody.getAttributeNames()
    )
    {
        if (
            !attribute.startsWith(
                'data-',
            )
        ) {
            continue;
        }

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

    // =====================================
    // RESTORE INTERNAL FLAGS
    // =====================================

    if (
        preserved.appInitialized
    ) {

        document.body.dataset
            .appInitialized =
                preserved.appInitialized;
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
            documentHtml,
            nextContent,
        } =
            parseHtml(
                html,
            );

        const currentContent =
            document.querySelector(
                CONTENT_SELECTOR,
            );

        if (
            !currentContent
        ) {

            throw new Error(
                'Missing current app content',
            );
        }

        // =================================
        // DOCUMENT
        // =================================

        updateDocumentMeta(
            documentHtml,
        );

        syncBodyAttributes(
            documentHtml,
        );

        // =================================
        // CONTENT
        // =================================

        currentContent.innerHTML =
            nextContent.innerHTML;

    } catch (error) {

        debugError(
            'ROUTER_DOM',
            error,
        );

        throw error;
    }
}