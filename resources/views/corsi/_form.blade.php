<div class="mb-4">
    <label for="nome" class="ui-label">Nome del corso</label>
    <x-ui.input type="text" :invalid="$errors->has('nome')" id="nome" name="nome" maxlength="255"
        value="{{ \App\Support\FormValue::old('nome', $corso->nome) }}" required />
    <x-ui.field-error for="nome" />
</div>

<div class="flex gap-2">
    <x-ui.button variant="primary" type="submit">Salva</x-ui.button>
    <x-ui.button variant="outline-secondary" href="{{ route('corsi.index') }}">Annulla</x-ui.button>
</div>
