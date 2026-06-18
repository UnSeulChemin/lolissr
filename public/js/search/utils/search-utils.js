// =========================================
// SEARCH UTILS
// =========================================

// =========================================
// ESCAPE HTML
// =========================================

export function escapeHtml(
    value,
)
{
    return String(
        value ?? '',
    )
        .replaceAll(
            '&',
            '&amp;',
        )
        .replaceAll(
            '<',
            '&lt;',
        )
        .replaceAll(
            '>',
            '&gt;',
        )
        .replaceAll(
            '"',
            '&quot;',
        )
        .replaceAll(
            "'",
            '&#039;',
        );
}

// =========================================
// ESCAPE REGEX
// =========================================

export function escapeRegExp(
    value,
)
{
    return String(
        value ?? '',
    ).replace(
        /[.*+?^${}()|[\]\\]/g,
        '\\$&',
    );
}

// =========================================
// NORMALIZE QUERY
// =========================================

export function normalizeSearchQuery(
    value,
)
{
    return String(
        value ?? '',
    )
        .trim()
        .toLowerCase();
}

// =========================================
// HIGHLIGHT SEARCH TERM
// =========================================

export function highlightSearchTerm(
    text,
    rawQuery,
)
{
    const safeText =
        escapeHtml(
            text,
        );

    const normalizedQuery =
        normalizeSearchQuery(
            rawQuery,
        );

    if (
        normalizedQuery === ''
    ) {
        return safeText;
    }

    const queryParts =
        normalizedQuery
            .split(
                /\s+/,
            )
            .filter(
                Boolean,
            )
            .map(
                escapeRegExp,
            );

    if (
        queryParts.length === 0
    ) {
        return safeText;
    }

    const regex =
        new RegExp(
            `(${queryParts.join('|')})`,
            'ig',
        );

    return safeText.replace(
        regex,
        '<mark class="search-highlight">$1</mark>',
    );
}

// =========================================
// IS EMPTY QUERY
// =========================================

export function isEmptyQuery(
    value,
)
{
    const query =
        normalizeSearchQuery(
            value,
        );

    return query === '';
}