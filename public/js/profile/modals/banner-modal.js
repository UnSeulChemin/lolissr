import { mountProfileModal } from './profile-modal-lifecycle.js';

// =================================================
// BANNIÈRE FENÊTRE MODALE
// =================================================

import { profileImageUrl } from '../../core/url.js';

// =================================================
// FENÊTRE MODALE
// =================================================

export function bannerModal(banners)
{
    return new Promise(
        (resolve) =>
        {
            const overlay = document.createElement('div');

            overlay.className = 'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">

                    <h3>
                        Choisir une bannière
                    </h3>

                    <div class="media-picker-grid banner-modal-grid">

                        ${banners.map(
                            (banner) => `
                                <button
                                    class="media-picker-item media-picker-item--banner banner-modal-item"
                                    data-banner="${banner.banner}"
                                    type="button"
                                    ${banner.unlocked ? '' : 'disabled'}
                                    aria-label="${banner.banner} — ${banner.unlocked ? 'Disponible' : 'Verrouillée'}, ${banner.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${profileImageUrl(`images/profil/banner/thumbnail/${banner.banner}.${banner.banner_extension}`)}"
                                        alt="${banner.banner}"
                                        draggable="false"
                                    >
                                    <span class="banner-modal-status">
                                        ${banner.unlocked ? '' : '<span aria-hidden="true">🔒</span> '}${banner.requirement}
                                    </span>

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            const close = mountProfileModal(overlay, resolve);

            overlay
                .querySelectorAll('.banner-modal-item:not(:disabled)')
                .forEach(
                    (button) =>
                    {
                        button.addEventListener('click', () => close(button.dataset.banner));
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
