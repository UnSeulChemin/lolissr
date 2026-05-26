// =========================================
// SEARCH BUILDERS
// =========================================

import {
    escapeHtml,
    highlightSearchTerm,
} from './search-utils.js';

// =========================================
// Manga Result
// =========================================

export function buildMangaSearchResult(
    manga,
    rawValue,
    basePath,
)
{
    const item =
        document.createElement(
            'a',
        );

    item.href =
        `${basePath}manga/series/${encodeURIComponent(manga.slug)}/${manga.numero}`;

    item.className =
        'search-result-item';

    item.innerHTML =
    `
        <img
            src="${basePath}images/mangas/thumbnail/${manga.thumbnail}.${manga.extension}"
            alt="${escapeHtml(manga.livre)}"
        >

        <span class="search-result-content">

            <strong class="search-result-title">
                ${highlightSearchTerm(
                    manga.livre,
                    rawValue,
                )}
            </strong>

            <small class="search-result-meta">
                Tome ${String(
                    manga.numero,
                ).padStart(
                    2,
                    '0',
                )}
            </small>

        </span>
    `;

    return item;
}

// =========================================
// Shortcut Result
// =========================================

export function buildShortcutSearchResult(
    shortcut,
    basePath,
)
{
    const item =
        document.createElement(
            'a',
        );

    item.href =
        `${basePath}${shortcut.url}`;

    item.className =
        'search-result-item';

    item.innerHTML =
    `
        <span class="search-result-icon">
            ${escapeHtml(
                shortcut.symbol,
            )}
        </span>

        <span class="search-result-content">

            <strong class="search-result-title">
                ${escapeHtml(
                    shortcut.title,
                )}
            </strong>

            <small class="search-result-meta">
                ${escapeHtml(
                    shortcut.description,
                )}
            </small>

        </span>
    `;

    return item;
}