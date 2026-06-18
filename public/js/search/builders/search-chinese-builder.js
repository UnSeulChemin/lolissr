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

    const langue =
        String(
            item.langue ?? '',
        ).toLowerCase();

    const niveau =
        String(
            item.niveau ?? '',
        ).toLowerCase();

    const icon =
        type === 'grammaire'
            ? '📖'
            : '📚';

    const label =
        type === 'grammaire'
            ? niveau.toUpperCase()
            : langue === 'jinyu'
                ? '晋语'
                : '中文';

    const url =
        type === 'grammaire'
            ? `${basePath}chinois/grammaire/${niveau}/recherche/${id}`
            : `${basePath}chinois/vocabulaire/${langue}/recherche/${id}`;

    return createResultItem(
        url,

        `
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${escapeHtml(icon)}
                </span>

                <span class="search-result-category-label">
                    ${escapeHtml(label)}
                </span>

            </span>

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