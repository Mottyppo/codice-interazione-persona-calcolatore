@extends('layouts.master')

@section('title', 'Configurazione conflitti')
@section('heading', 'Configurazione conflitti')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Configurazione</li>
@endsection

@section('content')
    <x-ui.card style="max-width: 40rem;">
        <x-ui.card-content>
            <p class="text-muted-foreground mb-2">
                Due appelli sono in conflitto quando cadono nella <strong>stessa data</strong>, le
                <strong>fasce orarie si sovrappongono</strong> e si verifica almeno una di queste condizioni:
            </p>
            <ul class="text-muted-foreground">
                <li><strong>conflitto studenti</strong> — gli insegnamenti appartengono allo <strong>stesso corso di
                    studio</strong> e allo <strong>stesso anno di frequenza</strong>;</li>
                <li><strong>conflitto aula</strong> — risulterebbe occupata la <strong>stessa aula</strong>.</li>
            </ul>
            <p class="text-muted-foreground">Scegli come gestire i conflitti al momento del salvataggio di un appello.</p>

            <form method="POST" action="{{ route('configurazione.update') }}">
                @csrf
                @method('PUT')

                <div class="ui-checkbox-field mb-4">
                    <input class="ui-checkbox" type="radio" name="modalita_conflitto" id="modalita_blocco"
                        aria-invalid="{{ $errors->has('modalita_conflitto') ? 'true' : 'false' }}" @if ($errors->has('modalita_conflitto')) aria-describedby="modalita_conflitto-error" @endif required
                        value="blocco" @checked(\App\Support\FormValue::old('modalita_conflitto', $configurazione->modalita_conflitto) === 'blocco')>
                    <label  for="modalita_blocco">
                        <strong>Blocco</strong> — impedisce il salvataggio in caso di conflitto.
                    </label>
                </div>

                <div class="ui-checkbox-field mb-4">
                    <input class="ui-checkbox" type="radio" name="modalita_conflitto" id="modalita_warning"
                        aria-invalid="{{ $errors->has('modalita_conflitto') ? 'true' : 'false' }}" @if ($errors->has('modalita_conflitto')) aria-describedby="modalita_conflitto-error" @endif required
                        value="warning" @checked(\App\Support\FormValue::old('modalita_conflitto', $configurazione->modalita_conflitto) === 'warning')>
                    <label  for="modalita_warning">
                        <strong>Avviso</strong> — consente il salvataggio ma segnala il conflitto.
                    </label>
                </div>

                <x-ui.field-error for="modalita_conflitto" />

                <hr>

                <div class="mb-4">
                    <label for="giorni_preappello" class="ui-label font-semibold">Preappelli</label>
                    <p id="giorni-preappello-help" class="text-muted-foreground ui-small mb-2">
                        Giorni di anticipo entro cui un docente può fissare un appello <strong>prima</strong> dell'inizio
                        della sessione, così gli studenti possono organizzarsi per lo studio. Imposta 0 per non ammettere preappelli.
                    </p>
                    <div class="ui-input-group" style="max-width: 16rem;">
                        <x-ui.input type="number" :invalid="$errors->has('giorni_preappello')"
                            id="giorni_preappello" name="giorni_preappello" aria-describedby="giorni-preappello-help" min="0" max="60"
                            value="{{ \App\Support\FormValue::old('giorni_preappello', $configurazione->giorni_preappello) }}" required />
                        <span class="ui-input-addon">giorni</span>
                    </div>
                    <x-ui.field-error for="giorni_preappello" />
                </div>

                <x-ui.button variant="primary" type="submit">Salva impostazioni</x-ui.button>
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
