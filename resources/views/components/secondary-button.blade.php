@props(['href' => null])

@php
    $attributes = $attributes->class('inline-flex items-center px-4 py-2 bg-card border border-input rounded-md font-semibold text-xs text-foreground uppercase tracking-widest shadow-sm hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150');
@endphp

@if ($href !== null)
    <a href="{{ $href }}" {{ $attributes }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</button>
@endif
