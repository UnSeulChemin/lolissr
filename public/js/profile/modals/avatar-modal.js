import { mountProfileModal } from './profile-modal-lifecycle.js';

// =================================================
// AVATAR FENÊTRE MODALE
// =================================================

import {
    appUrl,
} from '../../core/url.js';

// =================================================
// FENÊTRE MODALE
// =================================================

export function avatarModal(avatars)
{
    return new Promise(
        (resolve) =>
        {
            const overlay =
                document.createElement('div');

            overlay.className =
                'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">

                    <h3>
                        Choisir un avatar
                    </h3>

                    <div class="media-picker-grid media-picker-grid--avatars avatar-modal-grid">

                        ${avatars.map(
                            (avatar) => `
                                <button
                                    class="media-picker-item media-picker-item--avatar avatar-modal-item"
                                    data-avatar="${avatar.avatar}"
                                    type="button"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${appUrl(`images/profil/avatar/thumbnail/${avatar.avatar}.${avatar.avatar_extension}`)}"
                                        alt="${avatar.avatar}"
                                        draggable="false"
                                    >

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll('.avatar-modal-item')
                .forEach(
                    (button) =>
                    {
                        button.addEventListener(
                            'click',
                            () => close(button.dataset.avatar),
                        );
                    },
                );

            overlay.addEventListener(
                'click',
                (event) =>
                {
                    if (event.target === overlay)
                    {
                        close();
                    }
                },
            );
        },
    );
}
