// =================================================
// RENDU DE LA NAVIGATION
// =================================================

import {
    cachePage,
} from '../page-cache.js';
import {showToast} from '../../core/toast.js';

import {
    updateActiveNavigation,
} from '../router-active-link.js';

import {
    replaceContent,
} from '../router-dom.js';

import {
    clearActiveFocus,
} from '../router-focus.js';

import {
    activateScrollEntry,
    restoreScrollPosition,
} from '../route-scroll.js';

// =================================================
// RENDU
// =================================================

export async function renderPage(
    current,
    target,
    response,
    options,
)
{
    if (
        options.updateHistory !== false
    )
    {
        history.pushState(
            {},
            '',
            target,
        );
    }

    activateScrollEntry();

    if (
        typeof response.page.title === 'string'
    )
    {
        document.title =
            response.page.title;
    }

    cachePage(
        target,
        response,
    );

    replaceContent(
        response.page.html,
        response.page,
    );

    updateActiveNavigation();

    clearActiveFocus();

    const flash = response.page.flashToast;
    delete response.page.flashToast;
    if (typeof flash?.message === 'string' && flash.message !== '')
    {
        showToast(flash.message, flash.type ?? 'success');
    }

    if (options.updateHistory === false)
    {
        restoreScrollPosition();
        return;
    }

    // --------------------------------------------------------------------------
    // DÉFILEMENT VERS L’ANCRE
    // --------------------------------------------------------------------------


    if (
        window.location.hash
    )
    {
        queueMicrotask(
            () =>
            {
                let id = window.location.hash.slice(1);
                try { id = decodeURIComponent(id); } catch { // Conserver les séquences d’échappement invalides telles quelles.
 }
                document.getElementById(id)?.scrollIntoView();
            },
        );

        return;
    }

    // --------------------------------------------------------------------------
    // DÉFILEMENT PAR DÉFAUT
    // --------------------------------------------------------------------------


    window.scrollTo(
        0,
        0,
    );
}
