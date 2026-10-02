// =================================================
// CONFIGURATION DE L’APPLICATION
// =================================================

const hostname =
    window.location.hostname;

// =================================================
// ENVIRONNEMENT
// =================================================

const isLocalhost =
    hostname === 'localhost'
    || hostname === '127.0.0.1';

// =================================================
// DÉBOGAGE
// =================================================

const debugEnabled =
    isLocalhost
    && localStorage.getItem(
        'lolissr_debug',
    ) === '1';

// =================================================
// URI DE BASE
// =================================================

const baseUri =
    typeof window.appConfig?.baseUri === 'string'
        ? window.appConfig.baseUri
        : '/';

// =================================================
// ENVIRONNEMENT
// =================================================

const env =
    debugEnabled
        ? 'development'
        : 'production';

// =================================================
// CONFIGURATION
// =================================================

export const config =
    Object.freeze({

        // =================================================
        // APPLICATION
        // =================================================

        env,

        debug:
            debugEnabled,

        isLocalhost,

        baseUri,

        // =================================================
        // ROUTEUR
        // =================================================

        router:
        {
            timeout:
                10000,

            maxConcurrentNavigations:
                1,
        },

        // =================================================
        // PRÉCHARGEMENT
        // =================================================

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

        // =================================================
        // TRANSITIONS
        // =================================================

        transitions:
        {
            enabled:
                true,

            duration:
                250,
        },

        // =================================================
        // NAVIGATION
        // =================================================

        navigation:
        {
            backLockDuration:
                350,

            initialPrefetchDelay:
                800,

            restoreScroll:
                true,
        },

        // =================================================
        // DÉBOGAGE PANNEAU
        // =================================================

        debugPanel:
        {
            enabled:
                debugEnabled,

            maxLogs:
                30,
        },
    });