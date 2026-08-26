export function initEventReminders() {
    const dialogOpeners = new WeakMap();

    const openDialog = (button) => {
        const dialogId = button.getAttribute('aria-controls');
        const dialog = dialogId ? document.getElementById(dialogId) : null;

        if (!dialog || typeof dialog.showModal !== 'function') {
            return;
        }

        dialogOpeners.set(dialog, button);
        dialog.showModal();
        dialog.querySelector('input[type="password"]')?.focus();
    };

    document.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-event-reminder-open]');

        if (openButton) {
            openDialog(openButton);
            return;
        }

        const closeButton = event.target.closest('[data-event-reminder-close]');
        const dialog = closeButton?.closest('[data-event-reminder-dialog]');

        if (dialog) {
            dialog.close();
        }
    });

    document.addEventListener('close', (event) => {
        const dialog = event.target.closest?.('[data-event-reminder-dialog]');
        const opener = dialog ? dialogOpeners.get(dialog) : null;

        if (opener?.isConnected) {
            opener.focus({ preventScroll: true });
        }
    }, true);

    document.querySelectorAll('[data-event-reminder-dialog][data-open-on-load]')
        .forEach((dialog) => {
            if (typeof dialog.showModal === 'function' && !dialog.open) {
                dialog.showModal();
                dialog.querySelector('[aria-invalid="true"]')?.focus();
            }
        });
}
