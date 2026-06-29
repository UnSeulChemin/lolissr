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
// NORMALIZE BASE
// =========================================

export function normalizeBase(
    value = '',
)
{
    return String(
        value ?? '',
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
// GENERATE SLUG
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
// SEARCH QUERY
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
// AUTO SLUG
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

    // =====================================
    // PREVENT DOUBLE INIT
    // =====================================

    if (
        sourceInput.dataset.slugInitialized
        === 'true'
    ) {
        return;
    }

    sourceInput.dataset.slugInitialized =
        'true';

    function updateSlug()
    {
        targetInput.value =
            generateSlug(
                sourceInput.value,
            );
    }

    sourceInput.addEventListener(
        'input',
        updateSlug,
        {
            passive:
                true,
        },
    );

    updateSlug();
}