// =================================================
// UTILITAIRES DE RECHERCHE
// =================================================

// =================================================
// ÉCHAPPEMENT HTML
// =================================================

export function escapeHtml(value)
{
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// =================================================
// ÉCHAPPEMENT DES EXPRESSIONS RÉGULIÈRES
// =================================================

export function escapeRegExp(value)
{
    return String(value ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// =================================================
// NORMALISATION REQUÊTE
// =================================================

export function normalizeSearchQuery(value)
{
    return String(value ?? '')
        .trim()
        .toLowerCase();
}

// =================================================
// MISE EN ÉVIDENCE DU TERME RECHERCHÉ
// =================================================

let lastHighlightQuery;
let lastHighlightPattern;

export function highlightSearchTerm(text, rawQuery)
{
    const plainText = String(text ?? '');

    const normalizedQuery = normalizeSearchQuery(rawQuery);

    if (normalizedQuery === '')
    {
        return escapeHtml(plainText);
    }

    if (normalizedQuery !== lastHighlightQuery)
    {
        const queryParts = normalizedQuery
                .split(/\s+/)
                .filter(Boolean)
                .map(escapeRegExp);

        if (queryParts.length === 0)
        {
            return escapeHtml(plainText);
        }

        lastHighlightPattern = new RegExp(`(${queryParts.join('|')})`, 'ig');
        lastHighlightQuery = normalizedQuery;
    }
    const regex = lastHighlightPattern;

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
