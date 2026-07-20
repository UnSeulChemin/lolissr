// =========================================
// APP CONFIG
// =========================================

const hostname =
    window.location.hostname;

// =========================================
// ENV
// =========================================

const isLocalhost =
    hostname === 'localhost'
    || hostname === '127.0.0.1';

// =========================================
// DEBUG
// =========================================

const debugEnabled =
    isLocalhost
    && localStorage.getItem(
        'lolissr_debug',
    ) === '1';

// =========================================
// BASE URI
// =========================================

const baseUri =
    typeof window.appConfig?.baseUri === 'string'
        ? window.appConfig.baseUri
        : '/';

// =========================================
// ENVIRONMENT
// =========================================

const env =
    debugEnabled
        ? 'development'
        : 'production';

// =========================================
// CONFIG
// =========================================

export const config =
    Object.freeze({

        // =================================
        // APP
        // =================================

        env,

        debug:
            debugEnabled,

        isLocalhost,

        baseUri,

        // =================================
        // ROUTER
        // =================================

        router:
        {
            timeout:
                10000,

            maxConcurrentNavigations:
                1,
        },

        // =================================
        // PREFETCH
        // =========================================

        prefetch:
        {
            enabled:
                true,

            hoverDelay:
                80,

            timeout:
                8000,

            cacheLimit:
                50,

            cacheDuration:
                60000,
        },

        // =================================
        // TRANSITIONS
        // =================================

        transitions:
        {
            enabled:
                true,

            duration:
                250,
        },

        // =================================
        // NAVIGATION
        // =================================

        navigation:
        {
            backLockDuration:
                350,

            initialPrefetchDelay:
                800,

            restoreScroll:
                true,
        },

        // =================================
        // DEBUG PANEL
        // =================================

        debugPanel:
        {
            enabled:
                debugEnabled,

            maxLogs:
                30,
        },
    });