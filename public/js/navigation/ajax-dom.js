// =========================================
// AJAX DOM
// =========================================

import {
    $,
} from '../core/dom.js';

import {
    debug,
    debugError,
} from '../core/debug.js';

// =========================================
// Config
// =========================================

const AJAX_CONTAINER_SELECTOR =
    '.ajax-content';

// =========================================
// Helpers
// =========================================

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

function getAjaxContent(
    parent = document,
)
{
    return $(
        AJAX_CONTAINER_SELECTOR,
        parent,
    );
}

function updateDocumentMetadata(
    documentHtml,
)
{
    // =====================================
    // Title
    // =====================================

    const title =
        $(
            'title',
            documentHtml,
        );

    if (
        title?.textContent
    ) {

        document.title =
            title.textContent;
    }

    // =====================================
    // Language
    // =====================================

    const html =
        documentHtml.documentElement;

    if (
        html?.lang
    ) {

        document.documentElement.lang =
            html.lang;
    }
}

function validateAjaxResponse(
    documentHtml,
)
{
    return Boolean(
        getAjaxContent(
            documentHtml,
        ),
    );
}

// =========================================
// Replace Content
// =========================================

export function replaceContent(
    html,
)
{
    try {

        // =================================
        // Parse
        // =================================

        const documentHtml =
            parseHtml(
                html,
            );

        // =================================
        // Validate
        // =================================

        if (
            !validateAjaxResponse(
                documentHtml,
            )
        ) {

            throw new Error(
                'Invalid AJAX response',
            );
        }

        // =================================
        // Current Content
        // =================================

        const currentContent =
            getAjaxContent();

        if (
            !currentContent
            || !currentContent.isConnected
        ) {

            throw new Error(
                'Current AJAX container not found',
            );
        }

        // =================================
        // New Content
        // =================================

        const newContent =
            getAjaxContent(
                documentHtml,
            );

        if (!newContent)
        {
            throw new Error(
                'New AJAX content not found',
            );
        }

        // =================================
        // Metadata
        // =================================

        updateDocumentMetadata(
            documentHtml,
        );

        // =================================
        // Replace
        // =================================

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