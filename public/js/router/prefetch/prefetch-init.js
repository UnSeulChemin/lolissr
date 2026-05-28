// =========================================
// PREFETCH INIT
// =========================================

import {
    debug,
} from '../../core/debug/debug.js';

import {
    PREFETCH_STATE,
} from './prefetch-state.js';

import {
    bindPrefetch,
} from './prefetch-bind.js';

// =========================================
// INIT
// =========================================

export function initPrefetch()
{
    if (
        PREFETCH_STATE.initialized
    ) {

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