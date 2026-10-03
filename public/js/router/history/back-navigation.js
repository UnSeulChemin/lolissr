// =================================================
// GLOBAL RETOUR NAVIGATION
// =================================================

import { config } from '../../core/config.js';

import { navigateTo } from '../router-navigation.js';

import { debug } from '../../core/debug/debug.js';

// =================================================
// SÉLECTEURS
// =================================================

const TYPING_SELECTOR = `
input,
textarea,
select,
[contenteditable="true"]
`;

const INTERACTIVE_SELECTOR = `
a,
button,
[role="button"]
`;

// =================================================
// ÉTAT
// =================================================

let initialized = false;

let locked = false;

// =================================================
// UTILITAIRES
// =================================================

function hasClosest(target, selector)
{
    return (target instanceof Element && Boolean( target.closest( selector ) ));
}

function isTypingContext(target)
{
    return hasClosest(target, TYPING_SELECTOR);
}

function isInteractiveElement(target)
{
    return hasClosest(target, INTERACTIVE_SELECTOR);
}

// =================================================
// VERROU
// =================================================

function unlock()
{
    locked = false;
}

function lock()
{
    locked = true;
}

// =================================================
// RETOUR
// =================================================

function navigateBack()
{
    // --------------------------------------------------------------------------
    // VERROU
    // --------------------------------------------------------------------------

    if (locked)
    {

        debug('BACKSPACE', 'blocked');

        return;
    }

    lock();

    debug('BACKSPACE', 'navigate', location.pathname);

    // --------------------------------------------------------------------------
    // HISTORIQUE RETOUR
    // --------------------------------------------------------------------------

    if (window.history.length > 1)
    {

        window.history.back();

        requestAnimationFrame(unlock);

        return;
    }

    // --------------------------------------------------------------------------
    // REPLI
    // --------------------------------------------------------------------------

    void navigateTo(config.baseUri).finally(unlock);
}

// =================================================
// CLAVIER
// =================================================

function handleKeyboard(event)
{
    // --------------------------------------------------------------------------
    // CLÉ
    // --------------------------------------------------------------------------

    if (event.key !== 'Backspace')
    {

        return;
    }

    // --------------------------------------------------------------------------
    // RÉPÉTITION
    // --------------------------------------------------------------------------

    if (event.repeat)
    {

        return;
    }

    // --------------------------------------------------------------------------
    // MODIFICATEURS
    // --------------------------------------------------------------------------

    if (event.ctrlKey || event.metaKey || event.altKey || event.shiftKey)
    {

        return;
    }

    // --------------------------------------------------------------------------
    // SAISIE
    // --------------------------------------------------------------------------

    if (isTypingContext( event.target ))
    {

        return;
    }

    // --------------------------------------------------------------------------
    // INTERACTIF
    // --------------------------------------------------------------------------

    if (isInteractiveElement( event.target ))
    {

        return;
    }

    // --------------------------------------------------------------------------
    // ANNULATION DU COMPORTEMENT PAR DÉFAUT
    // --------------------------------------------------------------------------

    event.preventDefault();

    // --------------------------------------------------------------------------
    // NAVIGATION
    // --------------------------------------------------------------------------

    navigateBack();
}

// =================================================
// INITIALISATION
// =================================================

export function initGlobalBackNavigation()
{
    if (initialized)
    {

        debug('BACKSPACE', 'already-init');

        return;
    }

    initialized = true;

    document.addEventListener(
        'keydown',
        handleKeyboard,
        {
            passive: false
        }
    );

    debug('BACKSPACE', 'ready');
}