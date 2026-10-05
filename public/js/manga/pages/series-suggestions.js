// Suggestions from the current user's collection; no network requests.
export function initSeriesSuggestions(input, list, options)
{
    if (!list) return;
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLocaleLowerCase('fr');
    let active = -1;
    let matches = [];
    const close = () =>
    {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };
    const select = (option) =>
    {
        input.value = option.value;
        input.dispatchEvent(new Event('input', {bubbles: true}));
        close();
    };
    const render = () =>
    {
        const query = normalize(input.value);
        matches = options.filter((option) => normalize(option.value).includes(query))
            .sort((a, b) => Number(normalize(b.value).startsWith(query)) - Number(normalize(a.value).startsWith(query)));
        active = -1;
        input.removeAttribute('aria-activedescendant');
        list.replaceChildren();
        matches.forEach((option, index) =>
        {
            const item = document.createElement('div');
            item.id = `${list.id}-${index}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.className = 'manga-series-suggestion';
            const title = document.createElement('strong');
            title.textContent = option.value;
            const publisher = document.createElement('span');
            publisher.textContent = option.dataset.editeur;
            item.append(title, publisher);
            item.addEventListener('pointerdown', (event) =>
            {
                event.preventDefault();
                select(option);
            });
            list.append(item);
        });
        list.hidden = matches.length === 0;
        input.setAttribute('aria-expanded', String(matches.length > 0));
    };
    input.addEventListener('input', render);
    input.addEventListener('focus', render);
    input.addEventListener('blur', close);
    input.form?.addEventListener('reset', close);
    input.addEventListener('keydown', (event) =>
    {
        if (event.key === 'Escape')
        {
            close();
            return;
        }
        if (event.key === 'Enter' && !list.hidden && active >= 0)
        {
            event.preventDefault();
            select(matches[active]);
            return;
        }
        if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        event.preventDefault();
        if (list.hidden) render();
        if (!matches.length) return;
        active = active < 0 ? (event.key === 'ArrowDown' ? 0 : matches.length - 1)
            : (active + (event.key === 'ArrowDown' ? 1 : -1) + matches.length) % matches.length;
        [...list.children].forEach((item, index) => item.setAttribute('aria-selected', String(index === active)));
        const item = list.children[active];
        input.setAttribute('aria-activedescendant', item.id);
        item.scrollIntoView({block: 'nearest'});
    });
}
