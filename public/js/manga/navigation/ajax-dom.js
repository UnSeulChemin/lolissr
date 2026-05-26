// ==================================================
// AJAX DOM
// ==================================================

import {
    animateContentIn,
    animateContentOut,
} from './ajax-transitions.js';

/*
|------------------------------------------------------------------
| Selectors
|------------------------------------------------------------------
*/

const contentSelector =
    '.collection-ajax-content';

/*
|------------------------------------------------------------------
| Helpers
|------------------------------------------------------------------
*/

function getContent()
{
    return document.querySelector(
        contentSelector,
    );
}

function scrollToTop()
{
    window.scrollTo({
        top: 0,
        behavior: 'auto',
    });
}

/*
|------------------------------------------------------------------
| HTML
|------------------------------------------------------------------
*/

function parseHtml(
    html,
)
{
    const parser =
        new DOMParser();

    return parser.parseFromString(
        html,
        'text/html',
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
        title
        && title.textContent
    ) {

        document.title =
            title.textContent;
    }
}

function extractNewContent(
    documentHtml,
)
{
    return documentHtml.querySelector(
        contentSelector,
    );
}

/*
|------------------------------------------------------------------
| Replace Content
|------------------------------------------------------------------
*/

export async function replaceContent(
    html,
)
{
    const currentContent =
        getContent();

    if (!currentContent) {
        return;
    }

    const documentHtml =
        parseHtml(
            html,
        );

    updateDocumentTitle(
        documentHtml,
    );

    const newContent =
        extractNewContent(
            documentHtml,
        );

    if (!newContent) {

        throw new Error(
            '[AJAX] Missing content',
        );
    }

    /*
    |--------------------------------------------------------------
    | Fade Out
    |--------------------------------------------------------------
    */

    await animateContentOut(
        currentContent,
    );

    /*
    |--------------------------------------------------------------
    | Replace DOM
    |--------------------------------------------------------------
    */

    currentContent.innerHTML =
        newContent.innerHTML;

    /*
    |--------------------------------------------------------------
    | Scroll
    |--------------------------------------------------------------
    */

    scrollToTop();

    /*
    |--------------------------------------------------------------
    | Force Repaint
    |--------------------------------------------------------------
    */

    currentContent.offsetHeight;

    /*
    |--------------------------------------------------------------
    | Fade In
    |--------------------------------------------------------------
    */

    await animateContentIn(
        currentContent,
    );
}