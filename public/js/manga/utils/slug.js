// =========================================
// SLUG
// =========================================

const ACCENT_REGEX =
    /[\u0300-\u036f]/g;

const INVALID_SLUG_REGEX =
    /[^a-z0-9\s-]/g;

const MULTIPLE_SPACES_REGEX =
    /\s+/g;

const MULTIPLE_DASHES_REGEX =
    /-+/g;

const TRIM_DASHES_REGEX =
    /^-+|-+$/g;

// =========================================
// Normalize Base
// =========================================

export function normalizeBase(
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
            ACCENT_REGEX,
            '',
        );
}

// =========================================
// Generate Slug
// =========================================

export function generateSlug(
    value,
)
{
    return normalizeBase(
        value,
    )
        .replace(
            INVALID_SLUG_REGEX,
            '',
        )

        .replace(
            MULTIPLE_SPACES_REGEX,
            '-',
        )

        .replace(
            MULTIPLE_DASHES_REGEX,
            '-',
        )

        .replace(
            TRIM_DASHES_REGEX,
            '',
        );
}

// =========================================
// Normalize Search Query
// =========================================

export function normalizeSearchQuery(
    value,
)
{
    return generateSlug(
        value,
    );
}