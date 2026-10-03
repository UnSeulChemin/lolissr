// =================================================
// PRÉCHARGEMENT ASSOCIATION
// =================================================

import { config } from '../../core/config.js';

import { shouldIgnoreLink } from '../../core/navigation.js';

import { prefetchPage } from './prefetch-request.js';

// =================================================
// ASSOCIATION LIEN
// =================================================

function bindLink(link)
{
    // --------------------------------------------------------------------------
    // VALIDE LIEN
    // --------------------------------------------------------------------------

    if (!( link instanceof HTMLAnchorElement ))
    {

        return;
    }

    // --------------------------------------------------------------------------
    // IGNORER LIEN
    // --------------------------------------------------------------------------

    if (shouldIgnoreLink( link ))
    {

        return;
    }

    // --------------------------------------------------------------------------
    // SENSIBLES LIENS
    // --------------------------------------------------------------------------

    if (link.hasAttribute( 'data-confirm-logout' ) || link.pathname.endsWith( '/deconnexion' ))
    {

        return;
    }

    // --------------------------------------------------------------------------
    // DÉJÀ ASSOCIÉ
    // --------------------------------------------------------------------------

    if (link.dataset.prefetchBound === 'true')
    {

        return;
    }

    // --------------------------------------------------------------------------
    // MARQUAGE COMME ASSOCIÉ
    // --------------------------------------------------------------------------

    link.dataset.prefetchBound = 'true';

    let hoverTimer = null;

    // --------------------------------------------------------------------------
    // SURVOL PRÉCHARGEMENT
    // --------------------------------------------------------------------------

    link.addEventListener(
        'pointerenter',
        () =>
        {
            clearTimeout(hoverTimer);

            hoverTimer = window.setTimeout(
                    () =>
                    {
                        void prefetchPage(link.href);
                    },
                    config.prefetch.hoverDelay
                );
        },
        {
            passive: true
        }
    );

    // --------------------------------------------------------------------------
    // ANNULATION PRÉCHARGEMENT
    // --------------------------------------------------------------------------

    link.addEventListener(
        'pointerleave',
        () =>
        {
            clearTimeout(hoverTimer);
        },
        {
            passive: true
        }
    );
}

// =================================================
// ASSOCIATION PRÉCHARGEMENT
// =================================================

export function bindPrefetch()
{
    const links = document.querySelectorAll('a[data-prefetch]');

    for (const link of links)
    {
        bindLink(link);
    }
}