// =========================================
// ROUTER DOM
// =========================================

import {
    debug,
    debugError,
} from '../core/debug/debug.js';

import {
    FrontendError,
} from '../core/errors/FrontendError.js';

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

        throw new FrontendError(
            'Contenu application introuvable',
            {
                code:
                    'MISSING_APP_CONTENT',
            },
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
    /*
    |--------------------------------------------------------------------------
    | TITLE
    |--------------------------------------------------------------------------
    */

    const title =
        documentHtml.querySelector(
            'title',
        );

    if (
        title?.textContent
    ) {

        const nextTitle =
            title.textContent.trim();

        if (
            nextTitle
            !== document.title
        ) {

            document.title =
                nextTitle;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LANG
    |--------------------------------------------------------------------------
    */

    const nextLang =
        documentHtml.documentElement.lang;

    if (
        nextLang
        && nextLang
        !== document.documentElement.lang
    ) {

        document.documentElement.lang =
            nextLang;
    }
}

// =========================================
// SYNC BODY ATTRIBUTES
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

    /*
    |--------------------------------------------------------------------------
    | KEEP INTERNAL FLAGS
    |--------------------------------------------------------------------------
    */

    const preserved =
    {
        appInitialized:
            document.body.dataset
                .appInitialized,
    };

    /*
    |--------------------------------------------------------------------------
    | REMOVE OLD DATA ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    for (
        const attribute
        of document.body.getAttributeNames()
    )
    {
        if (
            !attribute.startsWith(
                'data-',
            )
        ) {

            continue;
        }

        document.body.removeAttribute(
            attribute,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APPLY NEW DATA ATTRIBUTES
    |--------------------------------------------------------------------------
    */

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
            value === null
        ) {

            continue;
        }

        document.body.setAttribute(
            attribute,
            value,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE INTERNAL FLAGS
    |--------------------------------------------------------------------------
    */

    if (
        preserved.appInitialized
    ) {

        document.body.dataset
            .appInitialized =
                preserved.appInitialized;
    }
}

// =========================================
// REPLACE DOM CONTENT
// =========================================

function replaceDomContent(
    currentContent,
    nextContent,
)
{
    currentContent.replaceChildren(
        ...nextContent.cloneNode(
            true,
        ).childNodes,
    );
}

// =========================================
// REPLACE CONTENT
// =========================================

export function replaceContent(
    html,
)
{
    try {

        /*
        |------------------------------------------------------------------
        | PARSE
        |------------------------------------------------------------------
        */

        const {
            documentHtml,
            nextContent,
        } =
            parseHtml(
                html,
            );

        /*
        |------------------------------------------------------------------
        | CURRENT CONTENT
        |------------------------------------------------------------------
        */

        const currentContent =
            document.querySelector(
                CONTENT_SELECTOR,
            );

        if (
            !currentContent
        ) {

            throw new FrontendError(
                'Contenu actuel introuvable',
                {
                    code:
                        'MISSING_CURRENT_CONTENT',
                },
            );
        }

        /*
        |------------------------------------------------------------------
        | DOCUMENT
        |------------------------------------------------------------------
        */

        updateDocumentMeta(
            documentHtml,
        );

        syncBodyAttributes(
            documentHtml,
        );

        /*
        |------------------------------------------------------------------
        | DOM SWAP
        |------------------------------------------------------------------
        */

        replaceDomContent(
            currentContent,
            nextContent,
        );

        debug(
            'ROUTER_DOM',
            'content-replaced',
        );

    } catch (error) {

        debugError(
            'ROUTER_DOM',
            error,
        );

        throw error;
    }
}
