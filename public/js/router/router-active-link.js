// =========================================
// ROUTER ACTIVE LINK
// =========================================

import {
    normalizeCacheKey,
} from '../core/navigation.js';

import {
    appUrl,
} from '../core/url.js';

// =========================================
// NORMALIZE PATH
// =========================================

function normalizePath(
    href,
)
{
    const url =
        new URL(
            normalizeCacheKey(
                href,
            ),
        );

    const pathname =
        url.pathname.replace(
            /\/+$/,
            '',
        );

    return pathname || '/';
}

// =========================================
// UPDATE ACTIVE NAVIGATION
// =========================================

export function updateActiveNavigation()
{
    const currentPath =
        normalizePath(
            location.href,
        );

    const homePath =
        normalizePath(
            appUrl(),
        );

    document
        .querySelectorAll(
            '.nav-link-icon, .site-profile-link',
        )
        .forEach(
            (link) =>
            {
                if (! (link instanceof HTMLAnchorElement))
                {
                    return;
                }

                const linkPath =
                    normalizePath(
                        link.href,
                    );

                const active =
                    linkPath === homePath
                        ? currentPath === homePath
                        : currentPath === linkPath
                            || currentPath.startsWith(
                                `${linkPath}/`,
                            );

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}