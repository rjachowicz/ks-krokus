const MIN_QUERY_LENGTH = 2;
const REQUEST_DELAY = 250;

const initCombobox = (root) => {
    const endpoint = root.dataset.endpoint;
    const searchInput = root.querySelector('[data-result-user-search]');
    const userIdInput = root.querySelector('[data-result-user-id]');
    const optionsList = root.querySelector('[data-result-user-options]');
    const status = root.querySelector('[data-result-user-status]');
    const clearButton = root.querySelector('[data-result-user-clear]');
    const participantInput = document.querySelector('[data-result-participant]');
    const clubInput = document.querySelector('[data-result-club]');
    const categorySelect = document.querySelector('[data-result-category]');

    if (!endpoint || !searchInput || !userIdInput || !optionsList || !status) {
        return;
    }

    let requestController = null;
    let requestTimer = null;
    let activeIndex = -1;
    let options = [];

    const announce = (message) => {
        status.textContent = message;
    };

    const setExpanded = (expanded) => {
        optionsList.hidden = !expanded;
        searchInput.setAttribute('aria-expanded', String(expanded));

        if (!expanded) {
            searchInput.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        }
    };

    const renderState = (message) => {
        options = [];
        activeIndex = -1;
        optionsList.replaceChildren();

        const item = document.createElement('li');
        item.className = 'result-user-options__state';
        item.setAttribute('role', 'option');
        item.setAttribute('aria-disabled', 'true');
        item.textContent = message;
        optionsList.append(item);
        setExpanded(true);
    };

    const activateOption = (index) => {
        if (options.length === 0) {
            return;
        }

        activeIndex = (index + options.length) % options.length;

        options.forEach((option, optionIndex) => {
            option.element.setAttribute('aria-selected', String(optionIndex === activeIndex));
        });

        const activeOption = options[activeIndex].element;
        searchInput.setAttribute('aria-activedescendant', activeOption.id);
        activeOption.scrollIntoView({ block: 'nearest' });
    };

    const clearSnapshot = () => {
        if (participantInput) {
            participantInput.value = '';
        }

        if (clubInput) {
            clubInput.value = '';
        }

        if (categorySelect) {
            categorySelect.value = '';
        }
    };

    const clearSelection = (clearFields = true) => {
        userIdInput.value = '';
        clearButton?.setAttribute('hidden', '');

        if (clearFields) {
            clearSnapshot();
        }
    };

    const selectUser = (user) => {
        userIdInput.value = String(user.id);
        searchInput.value = user.name;

        if (participantInput) {
            participantInput.value = user.name;
        }

        if (clubInput) {
            clubInput.value = user.club_name ?? '';
        }

        if (categorySelect) {
            categorySelect.value = user.category ?? '';
        }

        clearButton?.removeAttribute('hidden');
        setExpanded(false);
        announce(`Wybrano użytkownika: ${user.name}.`);
    };

    const renderOptions = (users) => {
        optionsList.replaceChildren();
        options = users.map((user, index) => {
            const item = document.createElement('li');
            item.id = `${optionsList.id}-option-${index + 1}`;
            item.className = 'result-user-options__option';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.tabIndex = -1;
            item.textContent = [user.name, user.category_label, user.club_name]
                .filter(Boolean)
                .join(' — ');
            item.addEventListener('pointerdown', (event) => event.preventDefault());
            item.addEventListener('click', () => selectUser(user));
            optionsList.append(item);

            return { element: item, user };
        });

        setExpanded(true);
        announce(`Liczba znalezionych użytkowników: ${users.length}.`);
    };

    const search = async (query) => {
        requestController?.abort();
        requestController = new AbortController();
        searchInput.setAttribute('aria-busy', 'true');
        renderState('Wyszukiwanie…');
        announce('Wyszukiwanie użytkowników…');

        try {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', query);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error('Błąd odpowiedzi');
            }

            const payload = await response.json();
            const users = Array.isArray(payload.data) ? payload.data : [];

            if (users.length === 0) {
                renderState('Brak pasujących użytkowników.');
                announce('Brak pasujących użytkowników.');
            } else {
                renderOptions(users);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                renderState('Nie udało się pobrać użytkowników. Spróbuj ponownie.');
                announce('Nie udało się pobrać użytkowników.');
            }
        } finally {
            searchInput.removeAttribute('aria-busy');
        }
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(requestTimer);

        if (userIdInput.value !== '') {
            clearSelection();
        }

        const query = searchInput.value.trim();

        if (query.length < MIN_QUERY_LENGTH) {
            requestController?.abort();
            setExpanded(false);
            announce(query.length === 0 ? '' : 'Wpisz co najmniej 2 znaki.');
            return;
        }

        requestTimer = window.setTimeout(() => search(query), REQUEST_DELAY);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            requestController?.abort();
            setExpanded(false);
            return;
        }

        if (optionsList.hidden || options.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activateOption(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activateOption(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            selectUser(options[activeIndex].user);
        }
    });

    searchInput.addEventListener('blur', () => {
        window.setTimeout(() => setExpanded(false), 100);
    });

    clearButton?.addEventListener('click', () => {
        clearSelection();
        searchInput.value = '';
        setExpanded(false);
        announce('Usunięto powiązanie z użytkownikiem. Wpisz dane zawodnika ręcznie.');
        searchInput.focus();
    });
};

export const initResultUserComboboxes = () => {
    document.querySelectorAll('[data-result-user-combobox]').forEach(initCombobox);
};
