export function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const close = () => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 180);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', close);

        if (!toast.hasAttribute('data-toast-persistent')) {
            let remaining = 8000;
            let startedAt = Date.now();
            let timeout = window.setTimeout(close, remaining);
            const pauseReasons = new Set();

            const pause = (reason) => {
                if (pauseReasons.has(reason)) {
                    return;
                }

                if (pauseReasons.size === 0) {
                    window.clearTimeout(timeout);
                    remaining -= Date.now() - startedAt;
                }

                pauseReasons.add(reason);
            };
            const resume = (reason) => {
                if (!pauseReasons.has(reason)) {
                    return;
                }

                pauseReasons.delete(reason);

                if (pauseReasons.size > 0) {
                    return;
                }

                if (remaining <= 0) {
                    close();

                    return;
                }

                startedAt = Date.now();
                timeout = window.setTimeout(close, remaining);
            };

            toast.addEventListener('mouseenter', () => pause('pointer'));
            toast.addEventListener('mouseleave', () => resume('pointer'));
            toast.addEventListener('focusin', () => pause('focus'));
            toast.addEventListener('focusout', (event) => {
                if (!toast.contains(event.relatedTarget)) {
                    resume('focus');
                }
            });
        }
    });
}
