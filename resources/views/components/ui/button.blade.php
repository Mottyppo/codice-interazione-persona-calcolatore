@props(['href' => null, 'type' => 'submit', 'variant' => 'primary', 'size' => 'default'])
@php
    $variants = [
        'primary' => 'ui-button-primary',
        'secondary' => 'ui-button-secondary',
        'danger' => 'ui-button-danger',
        'outline-primary' => 'ui-button-outline-primary',
        'outline-secondary' => 'ui-button-outline-secondary',
        'outline-danger' => 'ui-button-outline-danger',
    ];
    $classes = ['ui-button', $variants[$variant] ?? $variants['primary'], 'ui-button-sm' => $size === 'sm'];
@endphp
@if ($href !== null)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
