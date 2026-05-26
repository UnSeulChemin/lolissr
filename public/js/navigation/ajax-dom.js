// ==================================================
// AJAX DOM
// ==================================================

import {
    debug,
    debugError,
} from '../core/debug.js';

// ==================================================
// Config
// ==================================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

// ==================================================
// Helpers
// ==================================================

function getCurrentContent()
{
    return document.querySelector(
        AJAX_CONTAINER_SELECTOR,
    );
}

function parseHtml(
    html,
)
{
    return new DOMParser()
        .parseFromString(
            html,
            'text/html',
        );
}

function extractNewContent(
    documentHtml,
)
{
    return documentHtml.querySelector(
        AJAX_CONTAINER_SELECTOR,
    );
}

function updateDocumentTitle(
    documentHtml,
)
{
    const title =
        documentHtml.querySelector(
            'title',
        );

    if (
        !title?.textContent
    ) {
        return;
    }

    document.title =
        title.textContent;
}

function updateDocumentLanguage(
    documentHtml,
)
{
    const html =
        documentHtml.documentElement;

    if (
        !html?.lang
    ) {
        return;
    }

    document.documentElement.lang =
        html.lang;
}

function validateAjaxResponse(
    documentHtml,
)
{
    return Boolean(
        extractNewContent(
            documentHtml,
        ),
    );
}

// ==================================================
// Replace Content
// ==================================================

export function replaceContent(
    html,
)
{
    try {

        // ==========================================
        // Parse
        // ==========================================

        const documentHtml =
            parseHtml(
                html,
            );

        // ==========================================
        // Validate
        // ==========================================

        if (
            !validateAjaxResponse(
                documentHtml,
            )
        ) {

            throw new Error(
                'Invalid AJAX response',
            );
        }

        // ==========================================
        // Current content
        // ==========================================

        const currentContent =
            getCurrentContent();

        if (
            !currentContent
            || !currentContent.isConnected
        ) {

            throw new Error(
                'Current AJAX container not found',
            );
        }

        // ==========================================
        // New content
        // ==========================================

        const newContent =
            extractNewContent(
                documentHtml,
            );

        if (!newContent) {

            throw new Error(
                'New AJAX content not found',
            );
        }

        // ==========================================
        // Metadata
        // ==========================================

        updateDocumentTitle(
            documentHtml,
        );

        updateDocumentLanguage(
            documentHtml,
        );

        // ==========================================
        // Replace
        // ==========================================

        currentContent.replaceWith(
            newContent.cloneNode(
                true,
            ),
        );

        debug(
            'DOM',
            'content replaced',
        );

    } catch (error) {

        debugError(
            'DOM',
            error,
        );

        throw error;
    }
}