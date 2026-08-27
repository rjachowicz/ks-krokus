function setCaption(element, caption) {
    if (!element) return;

    element.textContent = caption;
    element.hidden = caption === '';
}

export function initMediaLightboxes() {
    const triggers = [...document.querySelectorAll('[data-media-lightbox-trigger]')];

    document.querySelectorAll('[data-media-lightbox]').forEach((dialog) => {
        const image = dialog.querySelector('[data-media-lightbox-image]');
        const caption = dialog.querySelector('[data-media-lightbox-caption]');
        const closeButton = dialog.querySelector('[data-media-lightbox-close]');
        const lightboxId = dialog.dataset.mediaLightbox;
        let opener = null;

        if (!image || !lightboxId) {
            return;
        }

        triggers
            .filter((trigger) => trigger.dataset.mediaLightboxTrigger === lightboxId)
            .forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    if (typeof dialog.showModal !== 'function') {
                        return;
                    }

                    event.preventDefault();
                    opener = trigger;
                    image.src = trigger.href;
                    image.alt = trigger.dataset.mediaLightboxAlt
                        || trigger.querySelector('img')?.alt
                        || '';
                    setCaption(caption, trigger.dataset.mediaLightboxCaption || '');

                    if (!dialog.open) {
                        dialog.showModal();
                    }

                    closeButton?.focus();
                });
            });

        closeButton?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
        dialog.addEventListener('close', () => {
            if (opener?.isConnected) {
                opener.focus({ preventScroll: true });
            }

            opener = null;
        });
    });
}
