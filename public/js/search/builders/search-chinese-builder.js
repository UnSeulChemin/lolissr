// =========================================
// SEARCH CHINESE BUILDER
// =========================================

import {
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

// =========================================
// BUILD CHINESE RESULT
// =========================================

export function buildChineseResult(
    item,
    basePath,
)
{
    const id =
        item.id ?? '';

    const type =
        item.type ?? '';

    const titre =
        item.titre ?? '';

    const description =
        item.description ?? '';

    const url =
        type === 'grammaire'
            ? `${basePath}chinois/grammaire/${id}`
            : `${basePath}chinois/vocabulaire/${id}`;

    return createResultItem(
        url,

        `
            <span class="search-result-content">

                <strong class="search-result-title">
                    ${escapeHtml(
                        titre,
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