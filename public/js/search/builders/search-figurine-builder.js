// =========================================
// SEARCH FIGURINE BUILDER
// =========================================

import {
    highlightSearchTerm,
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

export function buildFigurineResult(
    figurine,
    rawValue,
    basePath,
)
{
    const slug =
        encodeURIComponent(
            figurine.slug ?? '',
        );

    const numero =
        Number(
            figurine.numero ?? 0,
        );

    const waifu =
        figurine.waifu ?? '';

    const origin =
        figurine.origin ?? '';

    const thumbnail =
        figurine.thumbnail ?? 'default';

    const extension =
        figurine.extension ?? 'jpg';

    const imageUrl =
        `${basePath}images/figurine/thumbnail/${thumbnail}.${extension}`;

    const figurineUrl =
        `${basePath}figurine/waifus/${slug}/${numero}`;

    return createResultItem(
        figurineUrl,

        `
            <img
                src="${imageUrl}"
                alt="${escapeHtml(
                    waifu,
                )}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${highlightSearchTerm(
                        waifu,
                        rawValue,
                    )}
                </strong>

                <small class="search-result-meta">
                    ${escapeHtml(
                        origin,
                    )}
                </small>

            </span>
        `,
    );
}