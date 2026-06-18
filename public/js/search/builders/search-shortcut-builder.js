// =========================================
// SEARCH SHORTCUT BUILDER
// =========================================

import {
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

export function buildShortcutSearchResult(
    shortcut,
    basePath,
)
{
    const title =
        shortcut.title ?? '';

    const description =
        shortcut.description ?? '';

    const symbol =
        shortcut.symbol ?? '→';

    const url =
        shortcut.url ?? '';

    const shortcutUrl =
        `${basePath}${url}`;

    return createResultItem(
        shortcutUrl,

        `
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${escapeHtml(
                    symbol,
                )}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${escapeHtml(
                        title,
                    )}
                </strong>

                <small class="search-result-meta">
                    ${escapeHtml(
                        description,
                    )}
                </small>

            </span>
        `,
    );
}