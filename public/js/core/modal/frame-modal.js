export function frameModal(
    frames,
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
                        Choisir un cadre
                    </h3>

                    <div class="banner-modal-grid">

                        ${frames.map(
                            (
                                frame,
                            ) => `
                                <button
                                    class="banner-modal-item"
                                    data-frame="${frame.frame}"
                                    type="button"
                                >

                                    <img
                                        src="/lolissr/images/frames/thumbnail/${frame.frame}.${frame.frame_extension}"
                                        alt="${frame.frame}"
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
                                    button.dataset.frame,
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