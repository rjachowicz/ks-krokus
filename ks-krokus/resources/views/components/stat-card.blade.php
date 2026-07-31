@props([
    'value',
    'label',
])

<div {{ $attributes->class(['stat-card']) }}>
    <span class="stat-number">{{ $value }}</span>
    <span class="stat-label">{{ $label }}</span>
</div>
