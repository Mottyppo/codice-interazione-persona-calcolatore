@props([
    'action',
    'message' => "Confermi l'eliminazione? L'operazione non è reversibile.",
    'label' => 'Elimina',
])

<form method="POST" action="{{ $action }}" class="inline" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <x-ui.button type="submit" variant="outline-danger" size="sm" aria-label="{{ $label ?: 'Elimina' }}" {{ $attributes }}>
        <x-ui.icon name="trash" /> {{ $label }}
    </x-ui.button>
</form>
