// =========================================
// SEARCH NENDOROID BUILDER
// =========================================

import {
    highlightSearchTerm,
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

export function buildNendoroidResult(
    nendoroid,
    rawValue,
    basePath,
)
{
    const slug =
        encodeURIComponent(
            nendoroid.slug ?? '',
        );

    const numero =
        Number(
            nendoroid.numero ?? 0,
        );

    const waifu =
        nendoroid.waifu ?? '';

    const origin =
        nendoroid.origin ?? '';

    const thumbnail =
        nendoroid.thumbnail ?? 'default';

    const extension =
        nendoroid.extension ?? 'jpg';

    const imageUrl =
        `${basePath}images/nendoroid/thumbnail/${thumbnail}.${extension}`;

    const nendoroidUrl =
        `${basePath}nendoroid/waifus/${slug}/${numero}`;

    return createResultItem(
        nendoroidUrl,

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
                    ${highlightSearchTerm(
                        origin,
                        rawValue,
                    )}
                </small>

            </span>
        `,
    );
}