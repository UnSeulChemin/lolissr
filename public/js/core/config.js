// ==================================================
// App Config
// ==================================================

const basePath =
    window.location.pathname
        .startsWith('/lolissr/')
        ? '/lolissr/'
        : '/';

// ==================================================
// Config
// ==================================================

export const config =
{
    debug:
        window.location.hostname
            === 'localhost',

    baseUrl:
        basePath,

    ajax:
    {
        timeout:
            10000,
    },

    prefetch:
    {
        delay:
            80,

        timeout:
            8000,

        cooldown:
            3000,

        cacheLimit:
            50,
    },

    transitions:
    {
        duration:
            250,
    },

    navigation:
    {
        backLockDuration:
            350,

        initialPrefetchDelay:
            800,
    },
};