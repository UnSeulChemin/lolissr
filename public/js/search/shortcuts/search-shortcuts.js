// =========================================
// SEARCH SHORTCUTS
// =========================================

import {
    normalizeSearchQuery,
} from '../utils/search-utils.js';

// =========================================
// SHORTCUTS
// =========================================

export const SEARCH_SHORTCUTS =
    Object.freeze([
        {
            symbol:
                '一',

            title:
                'HSK1',

            description:
                'Débutant total',

            url:
                'chinois/grammaire/hsk1',
        },

        {
            symbol:
                '二',

            title:
                'HSK2',

            description:
                'Bases simples',

            url:
                'chinois/grammaire/hsk2',
        },

        {
            symbol:
                '三',

            title:
                'HSK3',

            description:
                'Intermédiaire débutant',

            url:
                'chinois/grammaire/hsk3',
        },

        {
            symbol:
                '四',

            title:
                'HSK4',

            description:
                'Intermédiaire solide',

            url:
                'chinois/grammaire/hsk4',
        },
    ]);

// =========================================
// FIND SEARCH SHORTCUTS
// =========================================

export function findSearchShortcuts(
    query,
)
{
    const normalizedQuery =
        normalizeSearchQuery(
            query,
        ).replaceAll(
            ' ',
            '',
        );

    if (
        normalizedQuery === ''
    ) {
        return [];
    }

    return SEARCH_SHORTCUTS.filter(
        (
            shortcut,
        ) =>
        {
            const searchableText =
            [
                shortcut.title,
                shortcut.symbol,
            ]
                .join(
                    ' ',
                )
                .toLowerCase()
                .replaceAll(
                    ' ',
                    '',
                );

            return searchableText.includes(
                normalizedQuery,
            );
        },
    );
}