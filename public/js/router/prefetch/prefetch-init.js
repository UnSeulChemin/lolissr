// =========================================
// PREFETCH INIT
// =========================================

import {
    config,
} from '../../core/config.js';

import {
    debug,
} from '../../core/debug/debug.js';

import {
    bindPrefetch,
} from './prefetch-bind.js';

import {
    PREFETCH_STATE,
} from './prefetch-state.js';

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    if (
        ! config.prefetch.enabled
        || PREFETCH_STATE.initialized
    )
    {
        return;
    }

    PREFETCH_STATE.initialized =
        true;

    bindPrefetch();

    document.addEventListener(
        'router:loaded',
        bindPrefetch,
    );

    debug(
        'PREFETCH',
        'ready',
    );
}