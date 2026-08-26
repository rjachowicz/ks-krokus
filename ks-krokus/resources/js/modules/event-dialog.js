function isPlainActivation(event) {
    return event.button === 0
        && !event.metaKey
        && !event.ctrlKey
        && !event.shiftKey
        && !event.altKey;
}

export function initEventDialogs() {
    const dialog = document.querySelector('[data-event-dialog]');
    const body = dialog?.querySelector('[data-event-dialog-body]');
    const closeButton = dialog?.querySelector('[data-event-dialog-close]');
    const triggers = [...document.querySelectorAll('[data-event-dialog-trigger]')];

    if (!dialog || !body || triggers.length === 0 || typeof dialog.showModal !== 'function') {
        return;
    }

    let activeTrigger = null;
    let activeUrl = null;
    let requestController = null;
    let requestNumber = 0;

    const renderState = (state, message) => {
        const stateContainer = document.createElement('div');
        stateContainer.className = `event-dialog__state event-dialog__state--${state}`;
        stateContainer.setAttribute('role', state === 'error' ? 'alert' : 'status');

        const title = document.createElement('h2');
        title.id = 'event-dialog-title';
        title.textContent = state === 'error' ? 'Nie udało się pobrać wydarzenia' : 'Ładowanie wydarzenia';

        const description = document.createElement('p');
        description.id = 'event-dialog-description';
        description.textContent = message;

        stateContainer.append(title, description);

        if (state === 'loading') {
            const spinner = document.createElement('span');
            spinner.className = 'event-dialog__spinner';
            spinner.setAttribute('aria-hidden', 'true');
            stateContainer.append(spinner);
        } else {
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'btn btn-primary';
            retry.textContent = 'Spróbuj ponownie';
            retry.addEventListener('click', () => loadEvent(activeUrl));
            stateContainer.append(retry);
        }

        body.replaceChildren(stateContainer);
        body.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
    };

    const renderEvent = (html) => {
        const template = document.createElement('template');
        template.innerHTML = html.trim();

        if (!template.content.querySelector('#event-dialog-title')
            || !template.content.querySelector('#event-dialog-description')
            || !template.content.querySelector('[data-event-details]')) {
            throw new Error('Nieprawidłowy fragment wydarzenia.');
        }

        body.replaceChildren(template.content);
        body.setAttribute('aria-busy', 'false');
    };

    const loadEvent = async (url) => {
        if (!url) {
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        const currentRequest = ++requestNumber;
        renderState('loading', 'Pobieramy szczegóły wydarzenia.');

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();

            if (currentRequest !== requestNumber || url !== activeUrl || !dialog.open) {
                return;
            }

            renderEvent(html);
        } catch (error) {
            if (error.name === 'AbortError' || currentRequest !== requestNumber || !dialog.open) {
                return;
            }

            renderState(
                'error',
                'Sprawdź połączenie i spróbuj ponownie. Pełny widok pozostaje dostępny z linku w kalendarzu.',
            );
            body.querySelector('button')?.focus();
        }
    };

    const openEvent = (trigger) => {
        const url = trigger.dataset.eventDialogUrl;

        if (!url || (dialog.open && url === activeUrl)) {
            return;
        }

        activeTrigger = trigger;
        activeUrl = url;

        if (!dialog.open) {
            dialog.showModal();
        }

        closeButton?.focus();
        loadEvent(url);
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            if (!isPlainActivation(event)) {
                return;
            }

            event.preventDefault();
            openEvent(trigger);
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key !== ' ' && event.key !== 'Spacebar') {
                return;
            }

            event.preventDefault();
            trigger.click();
        });
    });

    closeButton?.addEventListener('click', () => dialog.close());

    dialog.addEventListener('close', () => {
        requestController?.abort();
        requestController = null;
        requestNumber += 1;
        activeUrl = null;

        if (activeTrigger?.isConnected) {
            activeTrigger.focus({ preventScroll: true });
        }

        activeTrigger = null;
    });
}
