// =========================================
// SEARCH PELUCHE BUILDER
// =========================================

import {
    highlightSearchTerm,
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

export function buildPelucheResult(
    peluche,
    rawValue,
    basePath,
)
{
    const slug =
        encodeURIComponent(
            peluche.slug ?? '',
        );

    const numero =
        Number(
            peluche.numero ?? 0,
        );

    const waifu =
        peluche.waifu ?? '';

    const origin =
        peluche.origin ?? '';

    const thumbnail =
        peluche.thumbnail ?? 'default';

    const extension =
        peluche.extension ?? 'jpg';

    const imageUrl =
        `${basePath}images/peluche/thumbnail/${thumbnail}.${extension}`;

    const pelucheUrl =
        `${basePath}peluche/waifus/${slug}/${numero}`;

    return createResultItem(
        pelucheUrl,

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