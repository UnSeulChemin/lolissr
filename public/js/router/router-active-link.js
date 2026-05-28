// =========================================
// ROUTER ACTIVE LINK
// =========================================

import {
    normalizeUrl,
} from '../core/navigation.js';

// =========================================
// UPDATE ACTIVE NAVIGATION
// =========================================

export function updateActiveNavigation()
{
    const currentPath =
        location.pathname;

    document
        .querySelectorAll(
            '.nav-link-icon',
        )
        .forEach(
            (
                link,
            ) =>
            {
                if (
                    !(
                        link
                        instanceof HTMLAnchorElement
                    )
                ) {

                    return;
                }

                const normalizedLink =
                    normalizeUrl(
                        link.pathname,
                    );

                const active =
                    normalizedLink
                    === normalizeUrl(
                        '/lolissr/',
                    )
                        ? currentPath
                            === link.pathname
                        : currentPath.startsWith(
                            link.pathname,
                        );

                link.classList.toggle(
                    'active',
                    active,
                );
            },
        );
}