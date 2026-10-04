import { mountProfileModal } from './profile-modal-lifecycle.js';

// =================================================
// AVATAR FENÊTRE MODALE
// =================================================

import { profileImageUrl } from '../../core/url.js';

// =================================================
// FENÊTRE MODALE
// =================================================

export function avatarModal(avatars)
{
    return new Promise(
        (resolve) =>
        {
            const overlay = document.createElement('div');

            overlay.className = 'confirm-modal-overlay';

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
                                    ${avatar.unlocked ? '' : 'disabled'}
                                    aria-label="${avatar.avatar} — ${avatar.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${profileImageUrl(`images/profil/avatar/thumbnail/${avatar.avatar}.${avatar.avatar_extension}`)}"
                                        alt="${avatar.avatar}"
                                        draggable="false"
                                    >

                                    <span class="banner-modal-status">
                                        ${avatar.unlocked ? '' : '<span aria-hidden="true">🔒</span> '}${avatar.requirement}
                                    </span>

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll('.avatar-modal-item:not(:disabled)')
                .forEach(
                    (button) =>
                    {
                        button.addEventListener('click', () => close(button.dataset.avatar));
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
