@props([
    'value',
    'label',
])

<article {{ $attributes->class(['stat-card']) }}>
    <span class="stat-number">{{ $value }}</span>
    <span class="stat-label">{{ $label }}</span>
</article>
