@props(['name', 'crop' => null])
@foreach (['x', 'y', 'width', 'height'] as $coordinate)
    <input
        type="hidden"
        name="{{ $name }}[{{ $coordinate }}]"
        value="{{ is_array($crop) ? ($crop[$coordinate] ?? '') : '' }}"
        data-crop-field="{{ $coordinate }}"
        @disabled(! is_array($crop) || ! is_numeric($crop[$coordinate] ?? null))
    >
@endforeach
