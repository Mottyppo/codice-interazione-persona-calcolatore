@props(['name', 'label' => null])
<svg {{ $attributes->class(['ui-icon']) }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif><use href="{{ asset('icons.svg') }}#{{ $name }}"></use></svg>
