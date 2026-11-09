@props(['for'])

@error($for)
    <p {{ $attributes->merge(['id' => $for.'-error', 'class' => 'text-sm ui-text-danger']) }}>{{ $message }}</p>
@enderror
