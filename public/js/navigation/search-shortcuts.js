export const searchShortcuts = [
    {
        symbol: '一',
        title: 'HSK1',
        description: 'Débutant total',
        url: 'chinois/grammaire/hsk1',
    },

    {
        symbol: '二',
        title: 'HSK2',
        description: 'Bases simples',
        url: 'chinois/grammaire/hsk2',
    },

    {
        symbol: '三',
        title: 'HSK3',
        description: 'Intermédiaire débutant',
        url: 'chinois/grammaire/hsk3',
    },

    {
        symbol: '四',
        title: 'HSK4',
        description: 'Intermédiaire solide',
        url: 'chinois/grammaire/hsk4',
    },
];

export function findSearchShortcuts(query)
{
    const normalized =
        query
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '');

    if (normalized === '')
    {
        return [];
    }

    return searchShortcuts.filter((shortcut) =>
    {
        return shortcut.title
            .toLowerCase()
            .replace(/\s+/g, '')
            .includes(normalized);
    });
}