// =========================================
// SEARCH UTILS
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

export function highlightSearchTerm(
    text,
    rawQuery,
)
{
    const safeText =
        escapeHtml(
            text,
        );

    const trimmedQuery =
        String(
            rawQuery ?? '',
        ).trim();

    if (
        trimmedQuery === ''
    ) {
        return safeText;
    }

    const queryParts =
        trimmedQuery
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
        !queryParts.length
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