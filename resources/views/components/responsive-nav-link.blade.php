@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-accent-foreground bg-accent focus:outline-none focus:text-accent-foreground focus:bg-accent focus:border-ring transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted-foreground hover:text-foreground hover:bg-accent hover:border-ring focus:outline-none focus:text-foreground focus:bg-accent focus:border-ring transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
