// ==================================================
// Slug
// ==================================================

function normalizeBase(
    value,
)
{
    return value
        .toLowerCase()
        .trim()
        .normalize(
            'NFD',
        )
        .replace(
            /[\u0300-\u036f]/g,
            '',
        );
}

// ==================================================
// Generate Slug
// ==================================================

export function generateSlug(
    value,
)
{
    return normalizeBase(
        value,
    )
        // caractères autorisés
        .replace(
            /[^a-z0-9\s-]/g,
            '',
        )

        // espaces -> tirets
        .replace(
            /\s+/g,
            '-',
        )

        // tirets multiples
        .replace(
            /-+/g,
            '-',
        )

        // trim tirets
        .replace(
            /^-+|-+$/g,
            '',
        );
}

// ==================================================
// Normalize Search Query
// ==================================================

export function normalizeSearchQuery(
    value,
)
{
    return generateSlug(
        value,
    );
}