@props(['invalid' => false])
@php
    $errorId = $invalid && $attributes->get('id') ? $attributes->get('id').'-error' : '';
    $describedBy = trim($attributes->get('aria-describedby', '').' '.$errorId);
@endphp
<select {{ $attributes->except('aria-describedby')->merge(['aria-invalid' => $invalid ? 'true' : 'false', 'aria-describedby' => $describedBy ?: null])->class(['ui-select', 'ui-invalid' => $invalid]) }}>{{ $slot }}</select>
