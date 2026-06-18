// =========================================
// SEARCH MANGA BUILDER
// =========================================

import {
    highlightSearchTerm,
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

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

    const imageUrl =
        `${basePath}images/mangas/thumbnail/${thumbnail}.${extension}`;

    const mangaUrl =
        `${basePath}manga/series/${slug}/${numero}`;

    return createResultItem(
        mangaUrl,

        `
            <img
                src="${imageUrl}"
                alt="${escapeHtml(
                    livre,
                )}"
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