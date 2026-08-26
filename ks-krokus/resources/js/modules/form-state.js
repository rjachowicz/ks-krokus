const loadingLabels = new Map([
    ['filtruj', 'Filtrowanie…'],
    ['pokaż', 'Ładowanie…'],
    ['zaloguj się', 'Logowanie…'],
    ['wyloguj', 'Wylogowywanie…'],
    ['wyślij wiadomość', 'Wysyłanie…'],
    ['usuń', 'Usuwanie…'],
]);

function resolveLoadingLabel(button, form) {
    if (form.dataset.loadingLabel) {
        return form.dataset.loadingLabel;
    }

    const currentLabel = button?.textContent.trim().toLocaleLowerCase('pl') || '';

    if (currentLabel.startsWith('zapisz')) {
        return 'Zapisywanie…';
    }

    return loadingLabels.get(currentLabel) || 'Przetwarzanie…';
}

export function initFormStates() {
    const formStates = new Map();

    document.querySelectorAll('form').forEach((form) => {
        if (form.method.toLowerCase() === 'dialog') {
            return;
        }

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (formStates.has(form)) {
                event.preventDefault();

                return;
            }

            const submitter = event.submitter instanceof HTMLButtonElement
                ? event.submitter
                : form.querySelector('button[type="submit"]');
            const buttons = [...form.querySelectorAll('button[type="submit"]')];
            let submitterField = null;

            if (submitter?.name) {
                submitterField = document.createElement('input');
                submitterField.type = 'hidden';
                submitterField.name = submitter.name;
                submitterField.value = submitter.value;
                submitterField.dataset.submitterValue = '';
                form.append(submitterField);
            }

            const state = {
                buttons: buttons.map((button) => ({
                    button,
                    disabled: button.disabled,
                })),
                submitter,
                submitterField,
                submitterLabel: submitter?.textContent || '',
            };
            const loadingLabel = resolveLoadingLabel(submitter, form);

            formStates.set(form, state);
            form.setAttribute('aria-busy', 'true');

            buttons.forEach((button) => {
                button.disabled = true;
            });

            if (submitter) {
                submitter.textContent = loadingLabel;
                submitter.classList.add('is-loading');
            }

            let status = form.querySelector('[data-submit-status]');

            if (!status) {
                status = document.createElement('span');
                status.className = 'sr-only';
                status.dataset.submitStatus = '';
                status.setAttribute('role', 'status');
                status.setAttribute('aria-live', 'polite');
                form.append(status);
            }

            status.textContent = loadingLabel;
        });
    });

    window.addEventListener('pageshow', () => {
        formStates.forEach((state, form) => {
            form.removeAttribute('aria-busy');
            state.buttons.forEach(({ button, disabled }) => {
                button.disabled = disabled;
            });

            if (state.submitter) {
                state.submitter.textContent = state.submitterLabel;
                state.submitter.classList.remove('is-loading');
            }

            state.submitterField?.remove();

            const status = form.querySelector('[data-submit-status]');

            if (status) {
                status.textContent = '';
            }
        });

        formStates.clear();
    });
}
