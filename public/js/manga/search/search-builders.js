// =========================================
// SEARCH BUILDERS
// =========================================

import {
    escapeHtml,
    highlightSearchTerm,
} from './search-utils.js';

// =========================================
// MANGA RESULT
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
            loading="lazy"
            decoding="async"
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
// SHORTCUT RESULT
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
        <span
            class="search-result-icon"
            aria-hidden="true"
        >
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