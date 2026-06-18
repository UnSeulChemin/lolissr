// =========================================
// SEARCH SECTION RENDERER
// =========================================

export function appendSectionTitle(
    container,
    title,
)
{
    const section =
        document.createElement(
            'div',
        );

    section.className =
        'header-search-section-title';

    section.textContent =
        title;

    container.appendChild(
        section,
    );
}