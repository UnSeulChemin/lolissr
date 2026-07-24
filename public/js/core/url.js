// =========================================
// IMPORTS
// =========================================

import {
    config,
} from './config.js';

// =========================================
// APP URL
// =========================================

export function appUrl(path = '')
{
    return (
        config.baseUri
        + path
    ).replace(
        /\/{2,}/g,
        '/',
    );
}

// =========================================
// APP PATH
// =========================================

export function appPath(pathname = window.location.pathname)
{
    const baseUri =
        config.baseUri === '/'
            ? ''
            : config.baseUri.replace(
                /\/$/,
                '',
            );

    if (
        baseUri !== ''
        && pathname === baseUri
    ) {
        return '/';
    }

    if (
        baseUri !== ''
        && pathname.startsWith(
            `${baseUri}/`,
        )
    ) {
        return pathname.slice(
            baseUri.length,
        );
    }

    return pathname;
}