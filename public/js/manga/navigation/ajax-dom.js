// ==================================================
// AJAX DOM
// ==================================================

// ==================================================
// Selectors
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

function resetScroll()
{
    window.scrollTo({
        top: 0,
        behavior: 'auto',
    });
}

// ==================================================
// Replace Content
// ==================================================

export async function replaceContent(
    html,
)
{
    const currentContent =
        getContent();

    if (! currentContent) {

        throw new Error(
            '[AJAX DOM] Missing current content',
        );
    }

    // ==============================================
    // Parse
    // ==============================================

    const documentHtml =
        parseHtml(
            html,
        );

    // ==============================================
    // Title
    // ==============================================

    updateDocumentTitle(
        documentHtml,
    );

    // ==============================================
    // Extract
    // ==============================================

    const newContent =
        extractNewContent(
            documentHtml,
        );

    if (! newContent) {

        throw new Error(
            '[AJAX DOM] Missing new content',
        );
    }

    // ==============================================
    // Detached guard
    // ==============================================

    if (
        !document.body.contains(
            currentContent,
        )
    ) {
        return;
    }

    // ==============================================
    // Build fragment
    // ==============================================

    const fragment =
        document.createDocumentFragment();

    fragment.append(
        ...Array.from(
            newContent.childNodes,
        ).map(
            (node) =>
                node.cloneNode(
                    true,
                ),
        ),
    );

    // ==============================================
    // Replace
    // ==============================================

    currentContent.replaceChildren(
        fragment,
    );

    // ==============================================
    // Scroll
    // ==============================================

    resetScroll();
}