import { mountProfileModal } from './profile-modal-lifecycle.js';

// =========================================
// FRAME MODAL
// =========================================

import {
    appUrl,
} from '../url.js';

// =========================================
// MODAL
// =========================================

export function frameModal(frames, avatar)
{
    return new Promise(
        (resolve) =>
        {
            const overlay =
                document.createElement('div');

            overlay.className =
                'confirm-modal-overlay frame-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal frame-modal">

                    <h3>
                        Choisir un cadre
                    </h3>

                    <div class="title-modal-list frame-modal-grid">

                        ${frames.map(
                            (frame) => `
                                <button
                                    class="title-modal-item frame-modal-item"
                                    data-frame="${frame.frame}"
                                    type="button"
                                    ${frame.unlocked ? '' : 'disabled'}
                                    aria-label="${frame.frame} — ${frame.unlocked ? 'Disponible' : 'Verrouillé'}, ${frame.requirement}"
                                >

                                    <div class="profile-customization-avatar">

                                        <img loading="lazy" decoding="async"
                                            class="profile-avatar-image"
                                            src="${avatar}"
                                            alt=""
                                            draggable="false"
                                        >

                                        <img loading="lazy" decoding="async"
                                            class="profile-frame"
                                            src="${appUrl(`images/profil/frame/thumbnail/${frame.frame}.${frame.frame_extension}`)}"
                                            alt="${frame.frame}"
                                            draggable="false"
                                        >

                                    </div>

                                    <span class="frame-modal-status">
                                        ${frame.unlocked ? '' : '<span aria-hidden="true">🔒</span> '}${frame.requirement}
                                    </span>

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll('.frame-modal-item:not(:disabled)')
                .forEach(
                    (button) =>
                    {
                        button.addEventListener(
                            'click',
                            () => close(button.dataset.frame),
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
