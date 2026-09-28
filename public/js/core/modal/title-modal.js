import { mountProfileModal } from './profile-modal-lifecycle.js';

export function titleModal(
    titles,
)
{
    return new Promise(
        (
            resolve,
        ) =>
        {
            const overlay =
                document.createElement(
                    'div',
                );

            overlay.className =
                'confirm-modal-overlay title-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal title-modal" role="dialog" aria-modal="true" aria-label="Choisir un titre">

                    <h3>
                        Choisir un titre
                    </h3>

                    <div class="title-modal-list">

                        ${titles.map(
                            (
                                title,
                            ) => `
                                <button
                                    class="title-modal-item"
                                    data-title="${title.title}"
                                    type="button"
                                    ${title.unlocked ? '' : 'disabled'}
                                    aria-label="${title.title} — ${title.unlocked ? 'Disponible' : 'Verrouillé'}, niveau ${title.required_level}"
                                >
                                    <span>${title.title}</span>
                                    <span class="title-modal-status">
                                        ${title.unlocked ? (title.required_level > 1 ? `Niveau ${title.required_level}` : 'Disponible') : `<span aria-hidden="true">🔒</span> Niveau ${title.required_level}`}
                                    </span>
                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll(
                    '.title-modal-item:not(:disabled)',
                )
                .forEach(
                    (
                        button,
                    ) =>
                    {
                        button.addEventListener(
                            'click',
                            () =>
                                close(
                                    button.dataset.title,
                                ),
                        );
                    },
                );

            overlay.addEventListener(
                'click',
                (
                    event,
                ) =>
                {
                    if (
                        event.target
                        === overlay
                    ) {

                        close();
                    }
                },
            );
        },
    );
}
