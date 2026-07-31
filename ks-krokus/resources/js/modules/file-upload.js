function formatBytes(bytes) {
    if (bytes < 1024 * 1024) {
        return `${Math.ceil(bytes / 1024)} KB`;
    }

    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

export function initFileUploads() {
    document.querySelectorAll('[data-file-upload]').forEach((upload) => {
        const input = upload.querySelector('input[type="file"]');
        const preview = upload.querySelector('[data-file-preview]');
        const error = upload.querySelector('[data-file-error]');

        if (!input || !preview) {
            return;
        }

        if (error) {
            const errorId = error.id || `${input.name.replace(/[^a-z0-9]+/gi, '-')}-client-error`;
            const describedBy = input.getAttribute('aria-describedby');
            error.id = errorId;
            input.setAttribute(
                'aria-describedby',
                [describedBy, errorId].filter(Boolean).join(' '),
            );
        }

        const validate = () => {
            const files = [...input.files];
            const maxFiles = Number.parseInt(upload.dataset.maxFiles || '0', 10);
            const maxSizeKb = Number.parseInt(upload.dataset.maxSizeKb || '0', 10);
            let message = '';

            if (maxFiles > 0 && files.length > maxFiles) {
                message = `Możesz wybrać maksymalnie ${maxFiles} ${maxFiles === 1 ? 'plik' : 'plików'}.`;
            } else if (maxSizeKb > 0 && files.some((file) => file.size > maxSizeKb * 1024)) {
                message = `Każdy plik może mieć maksymalnie ${formatBytes(maxSizeKb * 1024)}.`;
            } else if (files.some((file) => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                message = 'Wybierz wyłącznie obrazy JPG, PNG lub WebP.';
            }

            input.setCustomValidity(message);

            if (error) {
                error.textContent = message;
                error.hidden = message === '';
            }

            upload.classList.toggle('is-invalid', message !== '');
        };

        const render = () => {
            preview.replaceChildren();
            validate();

            [...input.files].forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-preview';

                if (file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    const objectUrl = URL.createObjectURL(file);
                    image.src = objectUrl;
                    image.alt = '';
                    image.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
                    item.append(image);
                }

                const details = document.createElement('div');
                const name = document.createElement('strong');
                const size = document.createElement('span');
                name.textContent = file.name;
                size.textContent = formatBytes(file.size);
                details.append(name, size);

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'file-preview__remove';
                remove.textContent = 'Usuń';
                remove.setAttribute('aria-label', `Usuń plik ${file.name}`);
                remove.addEventListener('click', () => {
                    const transfer = new DataTransfer();
                    [...input.files].forEach((candidate, candidateIndex) => {
                        if (candidateIndex !== index) {
                            transfer.items.add(candidate);
                        }
                    });
                    input.files = transfer.files;
                    render();
                });

                item.append(details, remove);
                preview.append(item);
            });
        };

        input.addEventListener('change', render);
        upload.addEventListener('dragover', (event) => {
            event.preventDefault();
            upload.classList.add('is-dragging');
        });
        upload.addEventListener('dragleave', () => upload.classList.remove('is-dragging'));
        upload.addEventListener('drop', (event) => {
            event.preventDefault();
            upload.classList.remove('is-dragging');

            const transfer = new DataTransfer();
            const incoming = [...event.dataTransfer.files];
            const files = input.multiple ? [...input.files, ...incoming] : incoming.slice(0, 1);
            files.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            render();
        });
    });
}
