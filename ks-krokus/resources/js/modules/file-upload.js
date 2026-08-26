function formatBytes(bytes) {
    if (bytes < 1024 * 1024) {
        return `${Math.ceil(bytes / 1024)} KB`;
    }

    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function fileKey(file) {
    return `${file.name}:${file.size}:${file.lastModified}`;
}

export function initFileUploads() {
    document.querySelectorAll('[data-file-upload]').forEach((upload) => {
        const input = upload.querySelector('input[type="file"]');
        const preview = upload.querySelector('[data-file-preview]');
        const error = upload.querySelector('[data-file-error]');
        const form = upload.closest('form');
        const existingFiles = Number.parseInt(upload.dataset.existingFiles || '0', 10);
        const deletionInputs = existingFiles > 0
            ? [...(form?.querySelectorAll('[data-existing-file-delete]') || [])]
            : [];
        const coverRemove = input?.matches('[data-cover-file]')
            ? form?.querySelector('[data-cover-remove]')
            : null;
        const objectUrls = new Set();
        const cropValues = new Map();
        const cropEnabled = input?.hasAttribute('data-crop-enabled') === true;
        const cropName = input?.dataset.cropName || '';
        const cropAspect = input?.dataset.cropAspect || '1.3333333333';
        const existingCropScope = form?.querySelector('[data-existing-cover-crop]');
        let serverInvalid = input?.getAttribute('aria-invalid') === 'true';

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
            const removedExistingFiles = deletionInputs.filter((checkbox) => checkbox.checked).length;
            const totalFiles = Math.max(0, existingFiles - removedExistingFiles) + files.length;
            let message = '';

            if (maxFiles > 0 && totalFiles > maxFiles) {
                message = existingFiles > 0
                    ? `Galeria może zawierać łącznie maksymalnie ${maxFiles} zdjęć.`
                    : `Możesz wybrać maksymalnie ${maxFiles} ${maxFiles === 1 ? 'plik' : 'plików'}.`;
            } else if (maxSizeKb > 0 && files.some((file) => file.size > maxSizeKb * 1024)) {
                message = `Każdy plik może mieć maksymalnie ${formatBytes(maxSizeKb * 1024)}.`;
            } else if (files.some((file) => {
                const validMime = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
                const validExtension = /\.(?:jpe?g|png|webp)$/i.test(file.name);

                return file.type ? !validMime : !validExtension;
            })) {
                message = 'Wybierz wyłącznie obrazy JPG, PNG lub WebP.';
            }

            input.setCustomValidity(message);

            if (message !== '') {
                input.setAttribute('aria-invalid', 'true');
            } else if (!serverInvalid) {
                input.removeAttribute('aria-invalid');
            }

            if (error) {
                error.textContent = message;
                error.hidden = message === '';
            }

            upload.classList.toggle('is-invalid', message !== '');
        };

        const render = () => {
            preview.querySelectorAll('[data-crop-scope][data-crop-key]').forEach((scope) => {
                const values = {};
                ['x', 'y', 'width', 'height'].forEach((key) => {
                    values[key] = scope.querySelector(`[data-crop-field="${key}"]`)?.value || '';
                });
                cropValues.set(scope.dataset.cropKey, values);
            });
            objectUrls.forEach((objectUrl) => URL.revokeObjectURL(objectUrl));
            objectUrls.clear();
            preview.replaceChildren();
            validate();

            [...input.files].forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-preview';
                const key = fileKey(file);

                if (cropEnabled) {
                    item.dataset.cropScope = '';
                    item.dataset.cropKey = key;
                }

                if (file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    const objectUrl = URL.createObjectURL(file);
                    const releaseObjectUrl = () => {
                        URL.revokeObjectURL(objectUrl);
                        objectUrls.delete(objectUrl);
                    };

                    objectUrls.add(objectUrl);
                    image.src = objectUrl;
                    image.alt = '';
                    image.addEventListener('load', releaseObjectUrl, { once: true });
                    image.addEventListener('error', releaseObjectUrl, { once: true });
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

                const actions = document.createElement('div');
                actions.className = 'file-preview__actions';

                if (cropEnabled && cropName !== '') {
                    const crop = cropValues.get(key) || {};
                    ['x', 'y', 'width', 'height'].forEach((field) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.dataset.cropField = field;
                        hidden.name = input.multiple
                            ? `${cropName}[${index}][${field}]`
                            : `${cropName}[${field}]`;
                        hidden.value = crop[field] || '';
                        hidden.disabled = hidden.value === '';
                        item.append(hidden);
                    });

                    const cropButton = document.createElement('button');
                    cropButton.type = 'button';
                    cropButton.className = 'btn btn-secondary';
                    cropButton.textContent = 'Ustaw kadr';
                    cropButton.dataset.cropControl = '';
                    cropButton.dataset.cropSource = `#${input.id}`;
                    cropButton.dataset.cropFileIndex = String(index);
                    cropButton.dataset.cropAspect = cropAspect;
                    actions.append(cropButton);
                }

                actions.append(remove);

                item.append(details, actions);
                preview.append(item);
            });

            if (coverRemove) {
                coverRemove.disabled = input.files.length > 0;

                if (input.files.length > 0) {
                    coverRemove.checked = false;
                }
            }


            existingCropScope?.querySelectorAll('[data-crop-field]').forEach((field) => {
                const hasValue = field.value !== '';
                field.disabled = input.files.length > 0 || !hasValue;
            });
        };

        input.addEventListener('change', () => {
            serverInvalid = false;
            render();
        });
        deletionInputs.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                serverInvalid = false;
                validate();
            });
        });
        coverRemove?.addEventListener('change', () => {
            if (coverRemove.checked && input.files.length > 0) {
                input.value = '';
                render();
            }
        });
        upload.addEventListener('dragover', (event) => {
            event.preventDefault();
            upload.classList.add('is-dragging');
        });
        upload.addEventListener('dragleave', () => upload.classList.remove('is-dragging'));
        upload.addEventListener('drop', (event) => {
            event.preventDefault();
            upload.classList.remove('is-dragging');

            if (!event.dataTransfer) {
                return;
            }

            const transfer = new DataTransfer();
            const incoming = [...event.dataTransfer.files];
            const files = input.multiple ? [...input.files, ...incoming] : incoming.slice(0, 1);
            files.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            render();
        });
    });
}
