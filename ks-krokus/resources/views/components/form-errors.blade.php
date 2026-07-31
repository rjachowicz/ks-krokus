@if ($errors->any())
    <div {{ $attributes->class(['form-error-summary']) }} role="alert" tabindex="-1" data-error-summary>
        <strong>Popraw błędy w formularzu:</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
