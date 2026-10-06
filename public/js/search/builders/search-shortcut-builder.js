// =================================================
// CONSTRUCTION DES RACCOURCIS DE RECHERCHE
// =================================================

import { escapeHtml, highlightSearchTerm } from '../utils/search-utils.js';

import { createResultItem } from './search-result-item.js';

export function buildShortcutSearchResult(shortcut, basePath, query = '')
{
    const title = shortcut.title ?? '';

    const description = shortcut.description ?? '';

    const symbol = shortcut.symbol ?? '→';

    const url = shortcut.url ?? '';

    const shortcutUrl = `${basePath}${url}`;

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
                    ${highlightSearchTerm(
                        title,
                        query,
                    )}
                </strong>

                <small class="search-result-meta">
                    ${escapeHtml(
                        description,
                    )}
                </small>

            </span>
        `
    );
}
