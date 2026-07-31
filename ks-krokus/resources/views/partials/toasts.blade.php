@php
    $toastSuccess = session('success');
    $toastError = session('error');
@endphp

@if ($toastSuccess || $toastError || $errors->any())
    <div class="toast-region" aria-live="polite" aria-atomic="true">
        @if ($toastSuccess)
            <div class="toast toast--success" role="status" data-toast>
                <div>
                    <strong>Sukces</strong>
                    <p>{{ $toastSuccess }}</p>
                </div>
                <button type="button" class="toast__close" aria-label="Zamknij powiadomienie" data-toast-close>×</button>
            </div>
        @endif

        @if ($toastError || $errors->any())
            <div class="toast toast--error" role="alert" data-toast data-toast-persistent>
                <div>
                    <strong>Nie udało się wykonać operacji</strong>
                    <p>{{ $toastError ?: $errors->first() }}</p>
                </div>
                <button type="button" class="toast__close" aria-label="Zamknij powiadomienie" data-toast-close>×</button>
            </div>
        @endif
    </div>
@endif
