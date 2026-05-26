// =========================================
// PAGE TRANSITIONS (SPA CLEAN MODE)
// =========================================

import { debug } from './debug.js';

export function initPageTransitions()
{
    window.addEventListener('load', () =>
    {
        requestAnimationFrame(() =>
        {
            document.body.classList.add('page-ready');
            debug?.('TRANSITION', 'ready');
        });
    });
}

/**
 * OUT
 */
export function transitionOut(el)
{
    if (!el || !el.isConnected) return;

    el.classList.add('page-transition-out');
}

/**
 * IN
 */
export function transitionIn(el)
{
    if (!el || !el.isConnected) return;

    el.classList.remove('page-transition-out');
    el.classList.add('page-transition-enter');

    requestAnimationFrame(() =>
    {
        if (!el || !el.isConnected) return;
        el.classList.add('page-transition-visible');
    });
}

/**
 * SPA transition wrapper (instant mode)
 */
export function runPageTransition(cb)
{
    // ⚡ important: microtask safe wrapper
    // évite race conditions DOM swap
    return cb();
}

/**
 * Scroll helper
 */
export function scrollTop(smooth = false)
{
    // ⚠️ tu peux garder ou enlever cette protection
    // mais elle peut empêcher scroll sur navigation rapide
    window.scrollTo({
        top: 0,
        behavior: smooth ? 'smooth' : 'auto',
    });
}