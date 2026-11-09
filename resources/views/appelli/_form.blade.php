@php
    use Illuminate\Support\Str;
@endphp

<div class="ui-row ui-gutter-3">
    <div class="ui-col-md-8">
        <label for="insegnamento_id" class="ui-label">Insegnamento</label>
        <x-ui.select :invalid="$errors->has('insegnamento_id')" id="insegnamento_id" name="insegnamento_id" :aria-describedby="$insegnamenti->isEmpty() ? 'insegnamento-help' : null" required>
            <option value="">— Seleziona un insegnamento —</option>
            @foreach ($insegnamenti as $ins)
                <option value="{{ $ins->id }}" @selected((int) \App\Support\FormValue::old('insegnamento_id', $appello->insegnamento_id) === $ins->id)>
                    {{ $ins->nome }} ({{ $ins->corsoStudio->nome }} — {{ $ins->anno_frequenza }}° anno)
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.field-error for="insegnamento_id" />
        @if ($insegnamenti->isEmpty())
            <div id="insegnamento-help" class="ui-field-help ui-text-danger">Non hai insegnamenti assegnati: contatta l'amministratore.</div>
        @endif
    </div>

    <div class="ui-col-md-4">
        <label for="sessione_id" class="ui-label">Sessione</label>
        <x-ui.select :invalid="$errors->has('sessione_id')" id="sessione_id" name="sessione_id" :aria-describedby="$sessioni->isEmpty() ? 'sessione-help' : null" required>
            <option value="">— Seleziona —</option>
            @foreach ($sessioni as $sessione)
                <option value="{{ $sessione->id }}" @selected((int) \App\Support\FormValue::old('sessione_id', $appello->sessione_id) === $sessione->id)>
                    {{ $sessione->nome }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.field-error for="sessione_id" />
        @if ($sessioni->isEmpty())
            <div id="sessione-help" class="ui-field-help ui-text-danger">Nessuna sessione con finestra di inserimento aperta al momento.</div>
        @endif
    </div>

    <div class="ui-col-md-4">
        <label for="data" class="ui-label">Data</label>
        <x-ui.input type="date" :invalid="$errors->has('data')" id="data" name="data" min="{{ now()->format('Y-m-d') }}" aria-describedby="avviso-data{{ ($giorniPreappello ?? 0) > 0 ? ' data-help' : '' }}"
            value="{{ \App\Support\FormValue::old('data', $appello->data?->format('Y-m-d')) }}" required />
        <x-ui.field-error for="data" />
        @if (($giorniPreappello ?? 0) > 0)
            <div id="data-help" class="ui-field-help">Ammessi anche preappelli, fino a {{ $giorniPreappello }} giorni prima dell'inizio della sessione.</div>
        @endif
    </div>

    <div class="ui-col-md-4">
        <label for="ora_inizio" class="ui-label">Ora di inizio</label>
        <x-ui.input type="time" :invalid="$errors->has('ora_inizio')" id="ora_inizio" name="ora_inizio" aria-describedby="ora-inizio-help"
            value="{{ \App\Support\FormValue::old('ora_inizio', Str::substr((string) $appello->ora_inizio, 0, 5)) }}"
            min="08:00" max="18:00" required />
        <div id="ora-inizio-help" class="ui-field-help">Orario consentito: 08:00–18:00.</div>
        <x-ui.field-error for="ora_inizio" />
    </div>

    <div class="ui-col-md-4">
        <label for="ora_fine" class="ui-label">Ora di fine</label>
        <x-ui.input type="time" :invalid="$errors->has('ora_fine')" id="ora_fine" name="ora_fine" data-time-after="ora_inizio"
            value="{{ \App\Support\FormValue::old('ora_fine', Str::substr((string) $appello->ora_fine, 0, 5)) }}"
            min="08:00" max="18:00" required />
        <x-ui.field-error for="ora_fine" />
    </div>

    <div class="ui-col-md-6">
        <label for="aula" class="ui-label">Aula <span class="text-muted-foreground">(facoltativa)</span></label>
        <x-ui.input type="text" :invalid="$errors->has('aula')" id="aula" name="aula" maxlength="255"
            value="{{ \App\Support\FormValue::old('aula', $appello->aula) }}" />
        <x-ui.field-error for="aula" />
    </div>

    <div class="w-full">
        <label for="note" class="ui-label">Note <span class="text-muted-foreground">(facoltative)</span></label>
        <x-ui.textarea :invalid="$errors->has('note')" id="note" name="note" maxlength="1000" rows="2">{{ \App\Support\FormValue::old('note', $appello->note) }}</x-ui.textarea>
        <x-ui.field-error for="note" />
    </div>
</div>

{{-- Avviso giorno non lavorativo (weekend) e esito conflitti in tempo reale --}}
<input type="hidden" id="appello_id" value="{{ $appello->id }}">
<div id="avviso-data" aria-live="polite" class="mt-4"></div>
<div id="avviso-conflitto" aria-live="polite" class="mt-4"></div>

<div class="flex gap-2 mt-6">
    <x-ui.button variant="primary" type="submit">Salva</x-ui.button>
    <x-ui.button variant="outline-secondary" href="{{ route('appelli.index') }}">Annulla</x-ui.button>
</div>

@push('scripts')
<script>
    $(function () {
        const url = "{{ route('appelli.verifica-conflitto') }}";
        const $box = $('#avviso-conflitto');
        const $campi = $('#insegnamento_id, #data, #ora_inizio, #ora_fine, #aula');
        let richiesta = null;
        let versioneRichiesta = 0;

        function testoSicuro(valore) {
            return $('<span>').text(valore == null ? '' : String(valore)).html();
        }

        function verificaNonDisponibile() {
            $box.html('<div class="ui-alert ui-alert-warning mb-0 py-2">Verifica dei conflitti non disponibile. Correggi gli eventuali errori e riprova; i conflitti saranno controllati anche al salvataggio.</div>');
        }

        function etichettaMotivi(motivi) {
            const m = [];
            if (motivi && motivi.indexOf('studenti') !== -1) m.push('stesso corso e anno');
            if (motivi && motivi.indexOf('aula') !== -1) m.push('stessa aula');
            return m.length ? ' <span class="text-muted-foreground">[' + m.join(', ') + ']</span>' : '';
        }

        function verificaConflitto() {
            const versione = ++versioneRichiesta;
            if (richiesta) richiesta.abort();
            $box.empty();
            const insegnamento = $('#insegnamento_id').val();
            const data = $('#data').val();
            const oraInizio = $('#ora_inizio').val();
            const oraFine = $('#ora_fine').val();

            // Serve tutto compilato e una fascia coerente
            if (!insegnamento || !data || !oraInizio || !oraFine || oraFine <= oraInizio
                || !$('#data')[0].validity.valid || !$('#ora_inizio')[0].validity.valid || !$('#ora_fine')[0].validity.valid) {
                $box.empty();
                return;
            }

            richiesta = $.getJSON(url, {
                insegnamento_id: insegnamento,
                data: data,
                ora_inizio: oraInizio,
                ora_fine: oraFine,
                aula: $('#aula').val() || '',
                appello_id: $('#appello_id').val() || ''
            }).done(function (res) {
                if (versione !== versioneRichiesta) return;
                if (!res || typeof res.conflitto !== 'boolean' || !Array.isArray(res.dettagli)) {
                    verificaNonDisponibile();
                    return;
                }
                if (!res.conflitto) {
                    $box.html('<div class="ui-alert ui-alert-success mb-0 py-2"><svg class="ui-icon" aria-hidden="true"><use href="{{ asset('icons.svg') }}#check-circle"></use></svg> Nessun conflitto rilevato.</div>');
                    return;
                }

                const righe = res.dettagli.map(function (d) {
                    const motivo = etichettaMotivi(d.motivi);
                    return d.insegnamento
                        ? '<li>' + testoSicuro(d.insegnamento) + ' — ' + testoSicuro(d.docente) + ' (' + testoSicuro(d.orario) + ')' + motivo + '</li>'
                        : '<li>' + testoSicuro(d.anno) + '° anno, fascia ' + testoSicuro(d.orario) + motivo + '</li>';
                }).join('');

                $box.html(
                    '<div class="ui-alert ui-alert-warning mb-0">' +
                    '<strong><svg class="ui-icon" aria-hidden="true"><use href="{{ asset('icons.svg') }}#alert-triangle"></use></svg> Conflitto rilevato</strong> ' +
                    'con ' + testoSicuro(res.numero) + ' appello/i nella stessa data e fascia oraria:' +
                    '<ul class="mb-0 mt-1">' + righe + '</ul></div>'
                );
            }).fail(function (xhr, stato) {
                if (stato === 'abort' || versione !== versioneRichiesta) return;
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    document.dispatchEvent(new CustomEvent('field-validation-errors', { detail: xhr.responseJSON.errors }));
                }
                verificaNonDisponibile();
            });
        }

        // Avviso immediato se la data scelta cade nel weekend o in una festività
        const $avvisoData = $('#avviso-data');
        const FESTIVITA = @json($festivita ?? []);

        function verificaGiorno() {
            const valore = $('#data').val();
            if (!valore) { $avvisoData.empty(); return; }

            const parti = valore.split('-');
            const giorno = new Date(parti[0], parti[1] - 1, parti[2]).getDay();

            if (giorno === 0 || giorno === 6) {
                $avvisoData.html('<div class="ui-alert ui-alert-warning mb-0 py-2"><svg class="ui-icon" aria-hidden="true"><use href="{{ asset('icons.svg') }}#alert-triangle"></use></svg> La data scelta cade di sabato o domenica: non è possibile fissare un appello nel weekend.</div>');
            } else if (FESTIVITA[valore]) {
                $avvisoData.html('<div class="ui-alert ui-alert-warning mb-0 py-2"><svg class="ui-icon" aria-hidden="true"><use href="{{ asset('icons.svg') }}#alert-triangle"></use></svg> La data scelta è un giorno festivo (' + FESTIVITA[valore] + '): non è possibile fissare un appello.</div>');
            } else {
                $avvisoData.empty();
            }
        }

        $('#data').on('change', verificaGiorno);
        $campi.on('change', function () {
            ++versioneRichiesta;
            if (richiesta) richiesta.abort();
            $box.empty();
            // Native listeners clear previous custom validity while change bubbles.
            setTimeout(verificaConflitto, 0);
        });
        // Any edit invalidates an in-flight result before the next change request.
        $campi.on('input', function () {
            ++versioneRichiesta;
            if (richiesta) richiesta.abort();
            $box.empty();
        });

        // Verifica anche allo apertura, se il form è già compilato (modifica)
        verificaGiorno();
        verificaConflitto();
    });
</script>
@endpush
