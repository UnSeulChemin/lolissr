// =========================================
// SEARCH RESULT ITEM
// =========================================

export function createResultItem(
    href,
    content,
)
{
    const item =
        document.createElement(
            'a',
        );

    item.href =
        href;

    item.className =
        'search-result-item';

    item.innerHTML =
        content;

    return item;
}