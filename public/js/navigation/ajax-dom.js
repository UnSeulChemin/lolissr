// ==================================================
// AJAX DOM
// ==================================================

const contentSelector =
    '.ajax-content';

// ==================================================
// Helpers
// ==================================================

function getContent()
{
    return document.querySelector(
        contentSelector,
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
        contentSelector,
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
        title?.textContent
    ) {

        document.title =
            title.textContent;
    }
}

function isValidAjaxResponse(
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
    const documentHtml =
        parseHtml(
            html,
        );

    if (
        !isValidAjaxResponse(
            documentHtml,
        )
    ) {

        console.warn(
            '[AJAX DOM] Invalid AJAX response',
        );

        return;
    }

    const currentContent =
        getContent();

    if (
        !currentContent
        || !currentContent.isConnected
    ) {
        return;
    }

    const newContent =
        extractNewContent(
            documentHtml,
        );

    if (!newContent) {
        return;
    }

    updateDocumentTitle(
        documentHtml,
    );

    currentContent.replaceWith(
        newContent.cloneNode(true),
    );
}