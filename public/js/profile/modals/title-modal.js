import { mountProfileModal } from './profile-modal-lifecycle.js';

export function titleModal(titles)
{
    return new Promise(
        (resolve) =>
        {
            const overlay = document.createElement('div');

            overlay.className = 'confirm-modal-overlay title-modal-overlay';

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
                                    aria-label="${title.title} — ${title.unlocked ? 'Disponible' : 'Verrouillé'}, ${title.requirement}"
                                >
                                    <span data-title-style="${title.unlocked ? title.style : ''}">${title.title}</span>
                                    <span class="title-modal-status">
                                        ${title.unlocked ? '' : '<span aria-hidden="true">🔒</span> '}${title.requirement}
                                    </span>
                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll('.title-modal-item:not(:disabled)')
                .forEach(
                    (button) =>
                    {
                        button.addEventListener('click', () => close( button.dataset.title ));
                    }
                );

            overlay.addEventListener(
                'click',
                (event) =>
                {
                    if (event.target === overlay)
                    {

                        close();
                    }
                }
            );
        }
    );
}
