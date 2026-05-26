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
    value = '',
)
{
    return String(
        value,
    )
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
    value = '',
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
    value = '',
)
{
    return generateSlug(
        value,
    );
}

// =========================================
// INIT AUTO SLUG
// =========================================

export function initAutoSlug()
{
    const sourceInput =
        document.querySelector(
            '[data-slug-source]',
        );

    const targetInput =
        document.querySelector(
            '[data-slug-target]',
        );

    if (
        !sourceInput
        || !targetInput
    ) {

        return;
    }

    const updateSlug =
        () =>
        {
            targetInput.value =
                generateSlug(
                    sourceInput.value,
                );
        };

    sourceInput.removeEventListener(
        'input',
        updateSlug,
    );

    sourceInput.addEventListener(
        'input',
        updateSlug,
    );

    updateSlug();
}