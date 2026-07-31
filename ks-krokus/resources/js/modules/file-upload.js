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

        if (!input || !preview) {
            return;
        }

        const render = () => {
            preview.replaceChildren();

            [...input.files].forEach((file, index) => {
                const item = document.createElement('article');
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
