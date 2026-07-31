export function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const close = () => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 180);
        };

        toast.querySelector('[data-toast-close]')?.addEventListener('click', close);

        if (!toast.hasAttribute('data-toast-persistent')) {
            window.setTimeout(close, 6000);
        }
    });
}
