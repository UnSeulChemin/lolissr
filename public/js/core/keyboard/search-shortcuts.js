// ======================================================
// SEARCH SHORTCUTS
// ======================================================

export const searchShortcuts =
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

// ======================================================
// HELPERS
// ======================================================

function normalizeShortcutValue(
    value,
)
{
    return String(
        value ?? '',
    )
        .trim()
        .toLowerCase()
        .replace(
            /\s+/g,
            '',
        );
}

// ======================================================
// SEARCH
// ======================================================

export function findSearchShortcuts(
    query,
)
{
    const normalizedQuery =
        normalizeShortcutValue(
            query,
        );

    if (
        normalizedQuery === ''
    ) {
        return [];
    }

    return searchShortcuts.filter(
        (
            shortcut,
        ) =>
        {
            const normalizedTitle =
                normalizeShortcutValue(
                    shortcut.title,
                );

            return normalizedTitle.includes(
                normalizedQuery,
            );
        },
    );
}