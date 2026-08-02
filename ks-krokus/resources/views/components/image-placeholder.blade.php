@props([
    'label' => 'Brak zdjęcia',
])

<span {{ $attributes->class(['image-placeholder']) }} role="img" aria-label="{{ $label }}">
    <span class="image-placeholder__mark" aria-hidden="true">KS</span>
    <span>{{ $label }}</span>
</span>
