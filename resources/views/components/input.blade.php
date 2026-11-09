@props(['disabled' => false, 'errorKey' => null])
@php
    $fieldKey = $errorKey ?? $attributes->get('name') ?? \Illuminate\Support\Str::afterLast((string) $attributes->wire('model')->value(), '.');
    $invalid = $fieldKey && $errors->has($fieldKey);
    $errorId = $invalid && $attributes->get('id') ? $attributes->get('id').'-error' : '';
    $describedBy = trim($attributes->get('aria-describedby', '').' '.$errorId);
@endphp
<input @disabled($disabled) {{ $attributes->except('aria-describedby')->merge(['aria-invalid' => $invalid ? 'true' : 'false', 'aria-describedby' => $describedBy ?: null])->class(['border-input focus:border-ring focus:ring-ring rounded-md shadow-sm', 'ui-invalid' => $invalid]) }}>
