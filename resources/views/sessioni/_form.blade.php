<div class="ui-row ui-gutter-3">
    <div class="w-full">
        <label for="nome" class="ui-label">Nome della sessione</label>
        <x-ui.input type="text" :invalid="$errors->has('nome')" id="nome" name="nome" maxlength="255"
            value="{{ \App\Support\FormValue::old('nome', $sessione->nome) }}" required />
        <x-ui.field-error for="nome" />
    </div>

    <div class="ui-col-md-6">
        <label for="data_inizio" class="ui-label">Data di inizio</label>
        <x-ui.input type="date" :invalid="$errors->has('data_inizio')" id="data_inizio" name="data_inizio" aria-describedby="data-inizio-help"
            value="{{ \App\Support\FormValue::old('data_inizio', $sessione->data_inizio?->format('Y-m-d')) }}"
            min="{{ now()->addDay()->format('Y-m-d') }}" required />
        <div id="data-inizio-help" class="ui-field-help">Deve iniziare da domani in poi.</div>
        <x-ui.field-error for="data_inizio" />
    </div>

    <div class="ui-col-md-6">
        <label for="data_fine" class="ui-label">Data di fine</label>
        <x-ui.input type="date" :invalid="$errors->has('data_fine')" id="data_fine" name="data_fine" aria-describedby="data-fine-help"
            value="{{ \App\Support\FormValue::old('data_fine', $sessione->data_fine?->format('Y-m-d')) }}"
            min="{{ now()->addDays(7)->format('Y-m-d') }}" required />
        <div id="data-fine-help" class="ui-field-help">La sessione deve durare almeno una settimana.</div>
        <x-ui.field-error for="data_fine" />
    </div>
</div>

@push('scripts')
<script>
    // Il minimo della data di fine segue la data di inizio scelta: una settimana
    // dopo, estremi inclusi. Il vincolo vero resta comunque lato server.
    $(function () {
        const $inizio = $('#data_inizio');
        const $fine = $('#data_fine');

        $inizio.on('change', function () {
            const minimo = new Date((this.value || @json(now()->addDay()->format('Y-m-d'))) + 'T00:00:00Z');
            minimo.setUTCDate(minimo.getUTCDate() + 6);
            $fine.attr('min', minimo.toISOString().slice(0, 10));
            $fine[0].dispatchEvent(new Event('change', { bubbles: true }));
        }).trigger('change');
    });
</script>
@endpush

<div class="flex gap-2 mt-6">
    <button type="submit" class="ui-button ui-button-primary">Salva</button>
    <a href="{{ route('sessioni.index') }}" class="ui-button ui-button-outline-secondary">Annulla</a>
</div>
