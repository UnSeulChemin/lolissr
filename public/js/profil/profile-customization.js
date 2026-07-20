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

async function openTitleModal()
{
    const data =
        await get(appUrl('profil/ajax/titles'));

    const title =
        await titleModal(data.data.titles);

    if (! title)
    {
        return;
    }

    await post(
        appUrl('profil/ajax/update-title'),
        {
            title,
        },
    );

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

    invalidateProfilePages();

    showToast(
        'Titre mis à jour',
        'success',
    );
}

// =========================================
// OPEN AVATAR MODAL
// =========================================

async function openAvatarModal()
{
    const data =
        await get(appUrl('profil/ajax/avatars'));

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

    const avatarPath =
        appUrl(
            `images/avatar/thumbnail/${response.data.avatar}.${response.data.avatar_extension}`,
        );

    const customizationAvatar =
        document.querySelector('.profile-customization-avatar img');

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

    invalidateProfilePages();

    showToast(
        'Avatar mis à jour',
        'success',
    );
}

// =========================================
// OPEN BANNER MODAL
// =========================================

async function openBannerModal()
{
    const data =
        await get(appUrl('profil/ajax/banners'));

    const banner =
        await bannerModal(data.data.banners);

    if (! banner)
    {
        return;
    }

    await post(
        appUrl('profil/ajax/update-banner'),
        {
            banner,
        },
    );

    invalidateProfilePages();

    showToast(
        'Bannière mise à jour',
        'success',
    );
}

// =========================================
// OPEN FRAME MODAL
// =========================================

async function openFrameModal()
{
    const data =
        await get(appUrl('profil/ajax/frames'));

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

    await post(
        appUrl('profil/ajax/update-frame'),
        {
            frame,
        },
    );

    invalidateProfilePages();

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
    document
        .querySelector('.js-profile-title')
        ?.addEventListener(
            'click',
            () =>
            {
                void openTitleModal();
            },
        );

    document
        .querySelector('.js-profile-avatar')
        ?.addEventListener(
            'click',
            () =>
            {
                void openAvatarModal();
            },
        );

    document
        .querySelector('.js-profile-banner')
        ?.addEventListener(
            'click',
            () =>
            {
                void openBannerModal();
            },
        );

    document
        .querySelector('.js-profile-frame')
        ?.addEventListener(
            'click',
            () =>
            {
                void openFrameModal();
            },
        );
}