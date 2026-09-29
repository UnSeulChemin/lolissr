import { registerCleanup } from '../router/router-cleanup.js';

// =========================================
// PROFILE CUSTOMIZATION
// =========================================

import {
    get,
    post,
} from '../core/http.js';

import {
    avatarModal,
} from '../core/modal/avatar-modal.js';

import {
    bannerModal,
} from '../core/modal/banner-modal.js';

import {
    frameModal,
} from '../core/modal/frame-modal.js';

import {
    titleModal,
} from '../core/modal/modal.js';

import {
    showToast,
} from '../core/toast.js';

import {
    appUrl,
} from '../core/url.js';

import {
    invalidateProfilePages,
} from './profile-cache.js';

// =========================================
// OPEN TITLE MODAL
// =========================================

async function openTitleModal(signal)
{
    const data =
        await get(appUrl('profil/ajax/titles'), { signal });

    if (signal.aborted) return;

    const title =
        await titleModal(data.data.titles);

    if (! title)
    {
        return;
    }

    const titleResponse = await post(
        appUrl('profil/ajax/update-title'),
        {
            title,
        },
    );

    invalidateProfilePages();
    if (signal.aborted) return;

    document.querySelectorAll('.profile-customization-title, .profile-subtitle').forEach(element =>
    {
        element.dataset.titleStyle = titleResponse.data.style;
    });

    const customizationTitle =
        document.querySelector('.profile-customization-title');

    if (customizationTitle)
    {
        customizationTitle.textContent =
            title;
    }

    const profileSubtitle =
        document.querySelector('.profile-subtitle');

    if (profileSubtitle)
    {
        profileSubtitle.textContent =
            title;
    }

    showToast(
        'Titre mis à jour',
        'success',
    );
}

// =========================================
// OPEN AVATAR MODAL
// =========================================

async function openAvatarModal(signal)
{
    const data =
        await get(appUrl('profil/ajax/avatars'), { signal });

    if (signal.aborted) return;

    const avatar =
        await avatarModal(data.data.avatars);

    if (! avatar)
    {
        return;
    }

    const response =
        await post(
            appUrl('profil/ajax/update-avatar'),
            {
                avatar,
            },
        );

    invalidateProfilePages();
    if (signal.aborted) return;

    const avatarPath =
        appUrl(
            `images/profil/avatar/thumbnail/${response.data.avatar}.${response.data.avatar_extension}`,
        );

    const customizationAvatar =
        document.querySelector('.profile-customization-avatar img');

    document.querySelectorAll('.site-profile-avatar').forEach(image =>
    {
        image.src = avatarPath;
    });

    if (customizationAvatar)
    {
        customizationAvatar.src =
            avatarPath;
    }

    const profileAvatar =
        document.querySelector('.profile-avatar img');

    if (profileAvatar)
    {
        profileAvatar.src =
            avatarPath;
    }

    showToast(
        'Avatar mis à jour',
        'success',
    );
}

// =========================================
// OPEN BANNER MODAL
// =========================================

async function openBannerModal(signal)
{
    const data =
        await get(appUrl('profil/ajax/banners'), { signal });

    if (signal.aborted) return;

    const banner =
        await bannerModal(data.data.banners);

    if (! banner)
    {
        return;
    }

    const response = await post(
        appUrl('profil/ajax/update-banner'),
        {
            banner,
        },
    );

    invalidateProfilePages();
    if (signal.aborted) return;
    const imagePath = appUrl(
        `images/profil/banner/thumbnail/${response.data.banner}.${response.data.banner_extension}?v=20260929-sakura-v2`,
    );
    document.querySelectorAll('.profile-customization-banner img, .profile-banner img').forEach(image =>
    {
        image.src = imagePath;
    });

    showToast(
        'Bannière mise à jour',
        'success',
    );
}

// =========================================
// OPEN FRAME MODAL
// =========================================

async function openFrameModal(signal)
{
    const data =
        await get(appUrl('profil/ajax/frames'), { signal });

    if (signal.aborted) return;

    const avatar =
        document.querySelector('.profile-avatar-image');

    const frame =
        await frameModal(
            data.data.frames,
            avatar?.src ?? '',
        );

    if (! frame)
    {
        return;
    }

    const response = await post(
        appUrl('profil/ajax/update-frame'),
        {
            frame,
        },
    );

    invalidateProfilePages();
    if (signal.aborted) return;
    const imagePath = appUrl(
        `images/profil/frame/thumbnail/${response.data.frame}.${response.data.frame_extension}`,
    );
    document.querySelectorAll('.profile-customization-avatar .profile-frame, .profile-avatar .profile-frame, .site-profile-frame').forEach(image =>
    {
        image.src = imagePath;
    });

    showToast(
        'Cadre mis à jour',
        'success',
    );
}

// =========================================
// INIT
// =========================================

export function initProfileCustomization()
{
    const controller = new AbortController();
    let busy = false;
    registerCleanup(() => controller.abort());
    for (const [selector, open] of [
        ['.js-profile-title', openTitleModal],
        ['.js-profile-avatar', openAvatarModal],
        ['.js-profile-banner', openBannerModal],
        ['.js-profile-frame', openFrameModal],
    ])
    {
        document.querySelector(selector)?.addEventListener('click', async () =>
        {
            if (busy) return;
            busy = true;
            try { await open(controller.signal); }
            catch (error)
            {
                if (!controller.signal.aborted && error?.name !== 'AbortError')
                {
                    showToast('Impossible de modifier le profil. Réessaie.', 'error');
                }
            }
            finally { busy = false; }
        }, { signal: controller.signal });
    }
}
