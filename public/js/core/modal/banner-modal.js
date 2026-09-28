import { mountProfileModal } from './profile-modal-lifecycle.js';

// =========================================
// BANNER MODAL
// =========================================

import {
    appUrl,
} from '../url.js';

// =========================================
// MODAL
// =========================================

export function bannerModal(banners)
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
                                    aria-label="${banner.banner} — ${banner.unlocked ? 'Disponible' : 'Verrouillée'}, niveau ${banner.required_level}"
                                >

                                    <img
                                        src="${appUrl(`images/profil/banner/thumbnail/${banner.banner}.${banner.banner_extension}`)}"
                                        alt="${banner.banner}"
                                        draggable="false"
                                    >
                                    <span class="banner-modal-status">
                                        ${banner.unlocked ? (banner.required_level > 1 ? `Niveau ${banner.required_level}` : 'Disponible') : `<span aria-hidden="true">🔒</span> Niveau ${banner.required_level}`}
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
                        button.addEventListener(
                            'click',
                            () => close(button.dataset.banner),
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
