// =================================================
// ROUTEUR DOM
// =================================================

import { debug, debugError } from '../../core/debug/debug.js';

import { FrontendError } from '../../core/errors/frontend-error.js';

// =================================================
// CONFIGURATION
// =================================================

const CONTENT_SELECTOR = '.app-content';

// =================================================
// ANALYSE HTML
// =================================================

function parseHtml(html, page)
{
    if (page?.format === 'fragment')
    {
        const documentHtml = document.implementation.createHTMLDocument(page.title ?? '');
        documentHtml.documentElement.lang = page.lang ?? 'fr';
        for (const [name, value] of Object.entries(page.bodyData ?? {}))
        {
            documentHtml.body.dataset[name] = String(value);
        }
        const nextContent = documentHtml.createElement('main');
        nextContent.className = 'app-content';
        nextContent.innerHTML = html;
        documentHtml.body.append(nextContent);
        return {documentHtml, nextContent};
    }
    const documentHtml = new DOMParser()
            .parseFromString(html, 'text/html');

    const nextContent = documentHtml.querySelector(CONTENT_SELECTOR);

    if (!nextContent)
    {

        throw new FrontendError(
            'Contenu application introuvable',
            {
                code: 'MISSING_APP_CONTENT'
            }
        );
    }

    return {
        documentHtml,
        nextContent
    };
}

// =================================================
// MISE À JOUR DOCUMENT
// =================================================

function updateDocumentMeta(documentHtml)
{
    // --------------------------------------------------------------------------
    // TITRE
    // --------------------------------------------------------------------------

    const title = documentHtml.querySelector('title');

    if (title?.textContent)
    {

        const nextTitle = title.textContent.trim();

        if (nextTitle !== document.title)
        {

            document.title = nextTitle;
        }
    }

    // --------------------------------------------------------------------------
    // LANGUE
    // --------------------------------------------------------------------------

    const nextLang = documentHtml.documentElement.lang;

    if (nextLang && nextLang !== document.documentElement.lang)
    {

        document.documentElement.lang = nextLang;
    }
}

// =================================================
// SYNCHRONISATION CORPS ATTRIBUTS
// =================================================

function syncBodyAttributes(documentHtml)
{
    const nextBody = documentHtml.body;

    if (!nextBody)
    {

        return;
    }

    // --------------------------------------------------------------------------
    // CONSERVATION INTERNE INDICATEURS
    // --------------------------------------------------------------------------

    const preserved = {
        appInitialized: document.body.dataset
                .appInitialized
    };

    // --------------------------------------------------------------------------
    // SUPPRESSION ANCIENS ATTRIBUTS DE DONNÉES
    // --------------------------------------------------------------------------

    for (const attribute of document.body.getAttributeNames())
    {
        if (!attribute.startsWith( 'data-' ))
        {

            continue;
        }

        document.body.removeAttribute(attribute);
    }

    // --------------------------------------------------------------------------
    // APPLICATION NOUVEAUX ATTRIBUTS DE DONNÉES
    // --------------------------------------------------------------------------

    for (const attribute of nextBody.getAttributeNames())
    {
        if (!attribute.startsWith( 'data-' ))
        {

            continue;
        }

        const value = nextBody.getAttribute(attribute);

        if (value === null)
        {

            continue;
        }

        document.body.setAttribute(attribute, value);
    }

    // --------------------------------------------------------------------------
    // RESTAURATION INTERNE INDICATEURS
    // --------------------------------------------------------------------------

    if (preserved.appInitialized)
    {

        document.body.dataset
            .appInitialized = preserved.appInitialized;
    }
}

// =================================================
// REMPLACEMENT DOM CONTENU
// =================================================

function replaceDomContent(currentContent, nextContent)
{
    currentContent.replaceChildren(...nextContent.cloneNode( true ).childNodes);
}

// =================================================
// REMPLACEMENT CONTENU
// =================================================

export function replaceContent(html, page = {})
{
    try
    {

        // ------------------------------------------------------------------
        // ANALYSE
        // ------------------------------------------------------------------

        const {
            documentHtml,
            nextContent
        } = parseHtml(html, page);

        // ------------------------------------------------------------------
        // ACTUELLE CONTENU
        // ------------------------------------------------------------------

        const currentContent = document.querySelector(CONTENT_SELECTOR);

        if (!currentContent)
        {

            throw new FrontendError(
                'Contenu actuel introuvable',
                {
                    code: 'MISSING_CURRENT_CONTENT'
                }
            );
        }

        // ------------------------------------------------------------------
        // DOCUMENT
        // ------------------------------------------------------------------

        updateDocumentMeta(documentHtml);

        syncBodyAttributes(documentHtml);

        // ------------------------------------------------------------------
        // REMPLACEMENT DU DOM
        // ------------------------------------------------------------------

        replaceDomContent(currentContent, nextContent);

        debug('ROUTER_DOM', 'content-replaced');

    } catch (error)
    {

        debugError('ROUTER_DOM', error);

        throw error;
    }
}
