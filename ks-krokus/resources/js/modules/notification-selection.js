export function initNotificationSelection() {
    const selection = document.querySelector('[data-notification-selection]');

    if (!selection) {
        return;
    }

    const selectVisible = selection.querySelector('[data-notification-select-visible]');
    const counter = selection.querySelector('[data-notification-selected-count]');
    const submit = selection.querySelector('[data-notification-delete-selected]');
    const items = [...document.querySelectorAll('[data-notification-select]')];

    const updateState = () => {
        const selectedCount = items.filter((item) => item.checked).length;

        if (counter) {
            counter.textContent = `Zaznaczono: ${selectedCount}`;
        }

        if (submit) {
            submit.disabled = selectedCount === 0;
        }

        if (selectVisible) {
            selectVisible.checked = selectedCount > 0 && selectedCount === items.length;
            selectVisible.indeterminate = selectedCount > 0 && selectedCount < items.length;
        }
    };

    selectVisible?.addEventListener('change', () => {
        items.forEach((item) => {
            item.checked = selectVisible.checked;
        });
        updateState();
    });

    items.forEach((item) => item.addEventListener('change', updateState));

    if (selectVisible?.checked) {
        items.forEach((item) => {
            item.checked = true;
        });
    }

    updateState();
}
