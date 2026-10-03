// =================================================
// IMPORTS
// =================================================

import { config } from './config.js';

// =================================================
// URL DE L’APPLICATION
// =================================================

export function appUrl(path = '')
{
    return (config.baseUri + path).replace(/\/{2,}/g, '/');
}

// =================================================
// CHEMIN DE L’APPLICATION
// =================================================

export function appPath(pathname = window.location.pathname)
{
    const baseUri = config.baseUri === '/'
            ? ''
            : config.baseUri.replace(/\/$/, '');

    if (baseUri !== '' && pathname === baseUri)
    {
        return '/';
    }

    if (baseUri !== '' && pathname.startsWith( `${baseUri}/` ))
    {
        return pathname.slice(baseUri.length);
    }

    return pathname;
}