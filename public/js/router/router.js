// =================================================
// ROUTEUR
// =================================================

import { navigateTo } from './navigation/navigation.js';

import { updateActiveNavigation } from './ui/active-link.js';

import { clearActiveFocus } from './ui/focus.js';

import { activateScrollEntry } from './history/route-scroll.js';

import { debug } from '../core/debug/debug.js';

import { confirmModal } from '../core/modal/confirm-modal.js';

import { request } from '../core/http.js';
import { showToast } from '../core/toast.js';

import { shouldIgnoreLink } from '../core/navigation.js';

let logoutPending = false;

async function logout(link)
{
    if (logoutPending) return;
    logoutPending = true;
    try
    {
        const confirmed = await confirmModal({
            title: 'Déconnexion',
            message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
            confirmText: 'Déconnexion'
        });
        if (!confirmed) return;
        const response = await request(link.href, {method: 'POST'});
        if (response?.type === 'redirect')
        {
            window.location.href = response.redirect;
            return;
        }
        showToast('Déconnexion impossible. Réessaie.', 'error');
    }
    catch
    {
        showToast('Déconnexion impossible. Réessaie.', 'error');
    }
    finally
    {
        logoutPending = false;
    }
}

// =================================================
// CLIC
// =================================================

async function handleClick(event)
{
    if (event.defaultPrevented)
    {

        return;
    }

    if (event.button !== 0)
    {

        return;
    }

    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)
    {

        return;
    }

    const target = event.target;

    if (!( target instanceof Element ))
    {

        return;
    }

    const link = target.closest('a[href]');

    if (!( link instanceof HTMLAnchorElement ))
    {

        return;
    }

    if (link.hasAttribute( 'data-confirm-logout' ))
    {

        event.preventDefault();

        await logout(link);

        return;
    }

    if (shouldIgnoreLink( link ))
    {

        return;
    }

    if (link.hasAttribute('data-history-back') && window.history.length > 1)
    {
        event.preventDefault();
        clearActiveFocus();
        window.history.back();
        return;
    }

    event.preventDefault();

    clearActiveFocus();

    void navigateTo(link.href);
}

// =================================================
// POPSTATE
// =================================================

async function handlePopState()
{
    document.body.classList.add('no-route-animation');

    await navigateTo(
        location.href,
        {
            updateHistory: false,
            force: true
        }
    );

    requestAnimationFrame(
        () =>
        {
            document.body.classList.remove('no-route-animation');
        }
    );
}

// Un léger mouvement pendant un clic rapide ne doit pas emporter le lien.
function preventHeaderDrag(event)
{
    if (event.target instanceof Element && event.target.closest('header .nav-link-icon, header .site-profile-link'))
    {
        event.preventDefault();
    }
}

// =================================================
// INITIALISATION
// =================================================

export function initRouter()
{
    activateScrollEntry();
    history.scrollRestoration = 'manual';

    document.addEventListener('click', handleClick);

    document.addEventListener('dragstart', preventHeaderDrag);

    window.addEventListener('popstate', handlePopState);

    updateActiveNavigation();

    debug('ROUTER', 'ready');
}
