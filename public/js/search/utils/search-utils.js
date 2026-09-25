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
    const plainText = String(text ?? '');

    const normalizedQuery =
        normalizeSearchQuery(
            rawQuery,
        );

    if (
        normalizedQuery === ''
    ) {
        return escapeHtml(plainText);
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
        return escapeHtml(plainText);
    }

    const regex =
        new RegExp(
            `(${queryParts.join('|')})`,
            'ig',
        );

    return plainText
        .split(regex)
        .map((part, index) =>
        {
            const safePart = escapeHtml(part);

            return index % 2 === 1
                ? `<mark class="search-highlight">${safePart}</mark>`
                : safePart;
        })
        .join('');
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
