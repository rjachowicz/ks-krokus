export function initStickyHeader() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    let frameRequested = false;

    const updateHeader = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
        frameRequested = false;
    };

    const requestUpdate = () => {
        if (frameRequested) {
            return;
        }

        frameRequested = true;
        window.requestAnimationFrame(updateHeader);
    };

    updateHeader();
    window.addEventListener('scroll', requestUpdate, { passive: true });
}
