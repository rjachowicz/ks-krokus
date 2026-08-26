export function initConfirmations() {
    const dialog = document.querySelector('[data-confirm-dialog]');
    const dialogMessage = dialog?.querySelector('[data-confirm-message]');
    const dialogAccept = dialog?.querySelector('[data-confirm-accept]');
    let pendingForm = null;
    let pendingSubmitter = null;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            if (!dialog || typeof dialog.showModal !== 'function') {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }

                return;
            }

            event.preventDefault();
            pendingForm = form;
            pendingSubmitter = event.submitter;

            if (dialogMessage) {
                dialogMessage.textContent = form.dataset.confirm;
            }

            if (dialogAccept) {
                dialogAccept.textContent = form.dataset.confirmAction
                    || event.submitter?.textContent.trim()
                    || 'Potwierdź';
            }

            dialog.showModal();
        });
    });

    dialogAccept?.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        pendingForm.dataset.confirmed = 'true';
        pendingForm.requestSubmit(pendingSubmitter || undefined);
        delete pendingForm.dataset.confirmed;
        dialog.close();
    });

    dialog?.addEventListener('close', () => {
        pendingForm = null;
        pendingSubmitter = null;
    });

    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-confirmed]').forEach((form) => {
            delete form.dataset.confirmed;
        });
    });
}
