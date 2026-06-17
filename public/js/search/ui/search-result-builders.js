// =========================================
// SEARCH BUILDERS
// =========================================

import {
    escapeHtml,
    highlightSearchTerm,
} from '../utils/search-utils.js';

// =========================================
// CREATE RESULT ITEM
// =========================================

function createResultItem(
    href,
    content,
)
{
    const item =
        document.createElement(
            'a',
        );

    item.href =
        href;

    item.className =
        'search-result-item';

    item.innerHTML =
        content;

    return item;
}

// =========================================
// BUILD MANGA RESULT
// =========================================

export function buildMangaResult(
    manga,
    rawValue,
    basePath,
)
{
    const slug =
        encodeURIComponent(
            manga.slug ?? '',
        );

    const numero =
        Number(
            manga.numero ?? 0,
        );

    const livre =
        manga.livre ?? '';

    const thumbnail =
        manga.thumbnail ?? 'default';

    const extension =
        manga.extension ?? 'jpg';

    return createResultItem(
        `${basePath}manga/series/${slug}/${numero}`,

        `
            <img
                src="${basePath}images/mangas/thumbnail/${thumbnail}.${extension}"
                alt="${escapeHtml(livre)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${highlightSearchTerm(
                        livre,
                        rawValue,
                    )}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(
                        numero,
                    ).padStart(
                        2,
                        '0',
                    )}
                </small>

            </span>
        `,
    );
}

// =========================================
// BUILD SHORTCUT RESULT
// =========================================

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

    return createResultItem(
        `${basePath}${url}`,

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