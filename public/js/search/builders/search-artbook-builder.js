// =========================================
// SEARCH ARTBOOK BUILDER
// =========================================

import {
    highlightSearchTerm,
    escapeHtml,
} from '../utils/search-utils.js';

import {
    createResultItem,
} from './search-result-item.js';

export function buildArtbookResult(
    artbook,
    rawValue,
    basePath,
)
{
    const slug =
        encodeURIComponent(
            artbook.slug ?? '',
        );

    const numero =
        Number(
            artbook.numero ?? 0,
        );

    const title =
        artbook.artbook ?? '';

    const auteur =
        artbook.auteur ?? '';

    const serie =
        artbook.serie ?? '';

    const thumbnail =
        artbook.thumbnail ?? 'default';

    const extension =
        artbook.extension ?? 'jpg';

    const imageUrl =
        `${basePath}images/artbook/thumbnail/${thumbnail}.${extension}`;

    const artbookUrl =
        `${basePath}manga/artbooks/${slug}/${numero}`;

    const subtitle =
        serie
        || auteur
        || 'Artbook';

    return createResultItem(
        artbookUrl,

        `
            <img
                src="${imageUrl}"
                alt="${escapeHtml(
                    title,
                )}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${highlightSearchTerm(
                        title,
                        rawValue,
                    )}
                </strong>

                <small class="search-result-meta">
                    ${highlightSearchTerm(
                        subtitle,
                        rawValue,
                    )}
                </small>

            </span>
        `,
    );
}