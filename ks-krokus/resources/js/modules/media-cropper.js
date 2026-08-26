import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const VALUES = ['x', 'y', 'width', 'height'];

function readCrop(scope) {
    const crop = {};

    for (const key of VALUES) {
        const value = Number.parseFloat(scope.querySelector(`[data-crop-field="${key}"]`)?.value || '');
        if (!Number.isFinite(value)) return null;
        crop[key] = value;
    }

    return crop;
}

function writeCrop(scope, crop) {
    VALUES.forEach((key) => {
        const field = scope.querySelector(`[data-crop-field="${key}"]`);
        if (field) {
            field.disabled = false;
            field.value = String(Math.max(0, Math.min(1, crop[key])));
        }
    });
    scope.dispatchEvent(new CustomEvent('media-crop:changed', {
        bubbles: true,
        detail: { crop },
    }));
}

function sourceFor(control) {
    if (control.dataset.cropUrl) {
        return { url: control.dataset.cropUrl, revoke: false };
    }

    const input = document.querySelector(control.dataset.cropSource || '');
    const index = Number.parseInt(control.dataset.cropFileIndex || '0', 10);
    const file = input?.files?.[index];

    return file ? { url: URL.createObjectURL(file), revoke: true } : null;
}

export function initMediaCropper() {
    const dialog = document.querySelector('[data-media-cropper-dialog]');
    const image = dialog?.querySelector('[data-media-cropper-image]');
    const stage = dialog?.querySelector('[data-media-cropper-stage]');
    const save = dialog?.querySelector('[data-media-cropper-save]');
    const cancel = dialog?.querySelector('[data-media-cropper-cancel]');
    const reset = dialog?.querySelector('[data-media-cropper-reset]');
    const zoomIn = dialog?.querySelector('[data-media-cropper-zoom-in]');
    const zoomOut = dialog?.querySelector('[data-media-cropper-zoom-out]');

    if (!dialog || !image || !stage || !save || !cancel || !reset || !zoomIn || !zoomOut) return;

    let cropper = null;
    let activeScope = null;
    let activeControl = null;
    let activeSource = null;

    const close = () => {
        cropper?.destroy();
        cropper = null;
        image.removeAttribute('src');
        if (activeSource?.revoke) URL.revokeObjectURL(activeSource.url);
        activeSource = null;
        if (dialog.open) dialog.close();
        activeControl?.focus();
        activeControl = null;
        activeScope = null;
    };

    document.addEventListener('click', (event) => {
        const control = event.target.closest('[data-crop-control]');
        if (!control) return;

        const scope = control.closest('[data-crop-scope]');
        const source = sourceFor(control);
        if (!scope || !source || typeof dialog.showModal !== 'function') return;

        event.preventDefault();
        activeScope = scope;
        activeControl = control;
        activeSource = source;
        image.src = source.url;
        dialog.showModal();
        cancel.focus();

        cropper = new Cropper(image, {
            aspectRatio: Number.parseFloat(control.dataset.cropAspect || '1.3333333333'),
            autoCropArea: 1,
            background: false,
            checkOrientation: true,
            dragMode: 'move',
            guides: true,
            responsive: true,
            viewMode: 1,
            ready() {
                const current = readCrop(scope);
                const dimensions = cropper.getImageData();
                if (current) {
                    cropper.setData({
                        x: current.x * dimensions.naturalWidth,
                        y: current.y * dimensions.naturalHeight,
                        width: current.width * dimensions.naturalWidth,
                        height: current.height * dimensions.naturalHeight,
                    });
                }
            },
        });
    });

    save.addEventListener('click', () => {
        if (!cropper || !activeScope) return;
        const data = cropper.getData(true);
        const dimensions = cropper.getImageData();
        writeCrop(activeScope, {
            x: data.x / dimensions.naturalWidth,
            y: data.y / dimensions.naturalHeight,
            width: data.width / dimensions.naturalWidth,
            height: data.height / dimensions.naturalHeight,
        });
        close();
    });
    cancel.addEventListener('click', close);
    reset.addEventListener('click', () => cropper?.reset());
    zoomIn.addEventListener('click', () => cropper?.zoom(0.1));
    zoomOut.addEventListener('click', () => cropper?.zoom(-0.1));
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });
    stage.addEventListener('keydown', (event) => {
        const actions = {
            ArrowLeft: () => cropper?.move(-4, 0),
            ArrowRight: () => cropper?.move(4, 0),
            ArrowUp: () => cropper?.move(0, -4),
            ArrowDown: () => cropper?.move(0, 4),
            '+': () => cropper?.zoom(0.1),
            '=': () => cropper?.zoom(0.1),
            '-': () => cropper?.zoom(-0.1),
            r: () => cropper?.reset(),
            R: () => cropper?.reset(),
        };
        if (actions[event.key]) {
            event.preventDefault();
            actions[event.key]();
        }
    });
}
