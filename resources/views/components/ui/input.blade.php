@props(['invalid' => false])
@php
    $errorId = $invalid && $attributes->get('id') ? $attributes->get('id').'-error' : '';
    $describedBy = trim($attributes->get('aria-describedby', '').' '.$errorId);
@endphp
<input {{ $attributes->except('aria-describedby')->merge(['aria-invalid' => $invalid ? 'true' : 'false', 'aria-describedby' => $describedBy ?: null])->class(['ui-input', 'ui-invalid' => $invalid]) }}>
