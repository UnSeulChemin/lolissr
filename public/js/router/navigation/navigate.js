// =================================================
// NAVIGATION
// =================================================

import { debug, debugError } from '../../core/debug/debug.js';

import { end, finish, reset, start } from '../../core/debug/profiler.js';

import { normalizeCacheKey, normalizeRouteUrl } from '../../core/navigation.js';

import {
    emitNavigationAbort,
    emitNavigationError,
    emitNavigationFetch,
    emitNavigationReady,
    emitNavigationRender,
    emitNavigationStart
} from './navigation-events.js';

import { renderPage } from './navigation-render.js';

import { resolvePage } from './resolve-page.js';

import { validatePageResponse } from './validate-page-response.js';

import { preparePageStyles } from '../pages/styles.js';

import { clearInvalidatedRoute, shouldRefreshRoute } from '../pages/route-invalidation.js';

import { runCleanup } from '../lifecycle/cleanup.js';

import { dispatchRouterLoaded } from '../lifecycle/events.js';

import { triggerRouteChange } from '../lifecycle/hooks.js';

import { clearController, lockRouter, navigationState, setController, unlockRouter } from '../state.js';

import { saveScrollPosition } from '../history/route-scroll.js';

// =================================================
// NAVIGATION
// =================================================

export async function navigateTo(to, options = {})
{
    reset();

    start('total');

    const current = normalizeRouteUrl(location.href);

    const target = normalizeRouteUrl(to);

    const targetCacheKey = normalizeCacheKey(target);

    // --------------------------------------------------------------------------
    // MÊME ROUTE
    // --------------------------------------------------------------------------

    if (current === target && options.force !== true)
    {
        // La page peut déjà être affichée alors que ses modules s'initialisent.
        // Un nouveau clic sur cette destination doit laisser terminer la navigation.
        if (navigationState.controller && navigationState.target !== target)
        {
            ++navigationState.navigationId;
            navigationState.controller.abort();
            clearController();
            unlockRouter();
            emitNavigationAbort(current, target);
        }
        debug('ROUTER', 'same-route', target);

        return;
    }

    // --------------------------------------------------------------------------
    // ANNULATION PRÉCÉDENTE NAVIGATION
    // --------------------------------------------------------------------------

    navigationState.controller?.abort();

    // --------------------------------------------------------------------------
    // ENREGISTREMENT NAVIGATION
    // --------------------------------------------------------------------------

    const navigationId = ++navigationState.navigationId;

    lockRouter();

    const controller = new AbortController();

    setController(controller, target);

    // --------------------------------------------------------------------------
    // MÉMORISATION DU DÉFILEMENT
    // --------------------------------------------------------------------------

    saveScrollPosition();

    // --------------------------------------------------------------------------
    // DÉMARRAGE ÉVÉNEMENT
    // --------------------------------------------------------------------------

    emitNavigationStart(current, target);

    try
    {
        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        // --------------------------------------------------------------------------
        // INVALIDATION
        // --------------------------------------------------------------------------

        const forceRefresh = shouldRefreshRoute(targetCacheKey);

        if (forceRefresh)
        {
            clearInvalidatedRoute(targetCacheKey);
        }

        // --------------------------------------------------------------------------
        // RÉSOLUTION PAGE
        // --------------------------------------------------------------------------

        emitNavigationFetch(current, target);

        const response = await resolvePage(targetCacheKey, forceRefresh, controller.signal);

        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        // --------------------------------------------------------------------------
        // VALIDATION
        // --------------------------------------------------------------------------

        validatePageResponse(response);

        const commitPageStyles = await preparePageStyles(response.page.stylesheets, controller.signal);

        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        // --------------------------------------------------------------------------
        // NETTOYAGE
        // --------------------------------------------------------------------------

        start('cleanup');

        runCleanup();

        end('cleanup');

        // --------------------------------------------------------------------------
        // RENDU ÉVÉNEMENT
        // --------------------------------------------------------------------------

        emitNavigationRender(current, target);

        // --------------------------------------------------------------------------
        // RENDU PAGE
        // --------------------------------------------------------------------------

        start('render');

        commitPageStyles();

        await renderPage(current, target, response, options);

        end('render');

        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        // --------------------------------------------------------------------------
        // ÉVÉNEMENTS
        // --------------------------------------------------------------------------

        await triggerRouteChange({
            from: current,
            to: target
        });

        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        dispatchRouterLoaded(target);

        emitNavigationReady(current, target);

        debug('ROUTER', 'done', target);

        finish();
    }
    catch (error)
    {
        // --------------------------------------------------------------------------
        // ANNULATION
        // --------------------------------------------------------------------------

        if (error?.name === 'AbortError')
        {
            if (navigationId === navigationState.navigationId) emitNavigationAbort(current, target);

            return;
        }

        // --------------------------------------------------------------------------
        // PÉRIMÉE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId !== navigationState.navigationId)
        {

            return;
        }

        // --------------------------------------------------------------------------
        // ERREUR
        // --------------------------------------------------------------------------

        emitNavigationError(current, target, error);

        debugError('ROUTER', error);

        // --------------------------------------------------------------------------
        // REPLI
        // --------------------------------------------------------------------------

        if (options.fallback !== false)
        {
            window.location.href = target;
        }
    }
    finally
    {
        // --------------------------------------------------------------------------
        // LIBÉRATION ACTUELLE NAVIGATION
        // --------------------------------------------------------------------------

        if (navigationId === navigationState.navigationId)
        {
            clearController();
            unlockRouter();
        }
    }
}
