// =========================================
// ROUTER ACTIVE LINK
// =========================================

import {
    normalizeUrl,
} from '../core/navigation.js';

import {
    appUrl,
} from '../core/url.js';

// =========================================
// UPDATE ACTIVE NAVIGATION
// =========================================

export function updateActiveNavigation()
{
    const currentPath =
        normalizeUrl(location.pathname);

    const homePath =
        normalizeUrl(appUrl());

    document
        .querySelectorAll('.nav-link-icon, .site-profile-link')
        .forEach(
            (link) =>
            {
                if (! (link instanceof HTMLAnchorElement))
                {
                    return;
                }

                const linkPath =
                    normalizeUrl(link.pathname);

                const active =
                    linkPath === homePath
                        ? currentPath === homePath
                        : currentPath === linkPath
                            || currentPath.startsWith(`${linkPath}/`);

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}