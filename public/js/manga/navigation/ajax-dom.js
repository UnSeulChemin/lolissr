// ==================================================
// AJAX DOM
// ==================================================

const contentSelector =
    '.collection-ajax-content';

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

// ==================================================
// Replace Content
// ==================================================

export async function replaceContent(
    html,
)
{
    // ==============================================
    // Parse
    // ==============================================

    const documentHtml =
        parseHtml(
            html,
        );

    // ==============================================
    // Validate response
    // ==============================================

    const newContent =
        extractNewContent(
            documentHtml,
        );

    // Invalid AJAX response
    // Full reload fallback

    if (!newContent) {

        const redirectUrl =
            documentHtml.location?.href
            || window.location.href;

        window.location.assign(
            redirectUrl,
        );

        return;
    }

    // ==============================================
    // Current content
    // ==============================================

    const currentContent =
        getContent();

    if (
        !currentContent
        || !currentContent.isConnected
    ) {
        return;
    }

    // ==============================================
    // Title
    // ==============================================

    updateDocumentTitle(
        documentHtml,
    );

    // ==============================================
    // Build fragment
    // ==============================================

    const fragment =
        document.createDocumentFragment();

    const nodes =
        Array.from(
            newContent.childNodes,
        );

    for (const node of nodes) {

        fragment.appendChild(
            node.cloneNode(
                true,
            ),
        );
    }

    // ==============================================
    // Replace
    // ==============================================

    currentContent.replaceChildren(
        fragment,
    );
}