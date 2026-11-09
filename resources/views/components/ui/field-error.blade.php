@props(['for'])
@php
    $messages = array_unique(array_merge($errors->get($for), \Illuminate\Support\Arr::flatten($errors->get($for.'.*'))));
@endphp
@if ($messages)
    <div id="{{ $for }}-error" {{ $attributes->class(['ui-field-error']) }}>
        @foreach ($messages as $message)
            <p class="m-0">{{ $message }}</p>
        @endforeach
    </div>
@endif
