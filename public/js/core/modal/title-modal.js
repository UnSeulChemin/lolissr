export function titleModal(
    titles,
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
                        Choisir un titre
                    </h3>

                    <div class="title-modal-list">

                        ${titles.map(
                            (
                                title,
                            ) => `
                                <button
                                    class="title-modal-item"
                                    data-id="${title.id}"
                                    type="button"
                                >
                                    ${title.name}
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
                    '.title-modal-item',
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
                                    Number(
                                        button.dataset.id,
                                    ),
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
                    ) {

                        close();
                    }
                },
            );
        },
    );
}