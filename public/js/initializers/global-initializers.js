// ==================================================
// GLOBAL INITIALIZERS
// ==================================================

import {
    initRouter,
} from '../router/router.js';

import {
    initPrefetch,
} from '../router/prefetch/prefetch-init.js';

import {
    initNavigationLoading,
} from '../router/ui/navigation-loading.js';

import {
    initRouterDebugPanel,
} from '../router/ui/router-debug-panel.js';

import {
    initGlobalBackNavigation,
} from '../history/back-navigation.js';

import {
    initGlobalErrorHandlers,
} from '../boot/app-errors.js';

import {
    initCopy,
} from '../core/copy/copy-init.js';

// ==================================================
// EXPORT
// ==================================================

export const GLOBAL_INITIALIZERS = [

    [
        'Router',
        initRouter,
    ],

    [
        'Prefetch',
        initPrefetch,
    ],

    [
        'Copy',
        initCopy,
    ],

    [
        'NavigationLoading',
        initNavigationLoading,
    ],

    [
        'RouterDebugPanel',
        initRouterDebugPanel,
    ],

    [
        'GlobalBackNavigation',
        initGlobalBackNavigation,
    ],

    [
        'GlobalErrorHandlers',
        initGlobalErrorHandlers,
    ],
];