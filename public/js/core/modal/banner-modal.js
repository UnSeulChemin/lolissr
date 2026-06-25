export function bannerModal(
    banners,
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
                'confirm-modal-overlay';

            overlay.innerHTML = `
                <div class="confirm-modal">

                    <h3>
                        Choisir une bannière
                    </h3>

                    <div class="banner-modal-grid">

                        ${banners.map(
                            (
                                banner,
                            ) => `
                                <button
                                    class="banner-modal-item"
                                    data-banner="${banner.banner}"
                                    type="button"
                                >

                                    <img
                                        src="/lolissr/images/banners/thumbnail/${banner.banner}.${banner.banner_extension}"
                                        alt="${banner.banner}"
                                        draggable="false"
                                    >

                                </button>
                            `,
                        ).join('')}

                    </div>

                </div>
            `;

            document.body.append(
                overlay,
            );

            document.body.style.overflow =
                'hidden';

            const close =
                (
                    result = null,
                ) =>
                {
                    document.body.style.overflow =
                        '';

                    overlay.remove();

                    resolve(
                        result,
                    );
                };

                overlay
                    .querySelectorAll(
                        '.banner-modal-item',
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
                                    button.dataset.banner,
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
                    )
                    {
                        close();
                    }
                },
            );
        },
    );
}